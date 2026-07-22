<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Exceptions\PaymentGatewayException;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderRefundPendingNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Single owner of "turn the current cart into an order".
 *
 * Both the web checkout (CheckoutController) and the AI chatbot
 * (Chatbot::placeDirectOrder) go through here, which keeps the two entry points
 * in lockstep and makes the order+items+coupon write atomic.
 *
 * Everything that decides money or ownership is re-derived from the database
 * here rather than taken from the request or the session snapshot: item prices,
 * the shipping rate, the coupon, and the address owner. The payment webhook
 * already refuses to trust its payload; this applies the same rule to the step
 * that builds the order in the first place.
 *
 * STOCK & COUPON INVARIANT
 * Both are *reserved* the moment the order is written, and released together by
 * cancelAndRelease() if the order dies before it ships. Reserving at payment
 * time instead used to let ten customers check out against one remaining unit:
 * nine of them paid and only then discovered the shop had nothing to send. The
 * reservation moves that failure to checkout, where it can still be refused.
 */
class OrderService
{
    public function __construct(
        protected CartService $cart,
        protected PakasirService $pakasir,
        protected BiteshipService $biteship,
    ) {}

    /**
     * Create an order from the current cart, generate its payment URL, clear the
     * cart and notify the customer.
     *
     * `shipping_cost` is deliberately NOT accepted from the caller — it is
     * re-quoted from Biteship for the chosen courier and service.
     *
     * @param  array{address_id:int, shipping_courier:string, shipping_service:string, payment_method?:string, notes?:string|null}  $params
     * @return array{order: Order, paymentUrl: string}
     *
     * @throws CheckoutException when the request no longer holds (bad address,
     *                           stale shipping option, invalid coupon, stock
     *                           that ran out while the customer was deciding).
     * @throws PaymentGatewayException when the gateway cannot issue a payment
     *                                 link. The half-written order is rolled back before throwing.
     */
    public function createFromCart(User $user, array $params): array
    {
        $items = $this->cart->getItems();

        if (empty($items)) {
            throw new CheckoutException('Keranjang Anda kosong.');
        }

        // The address id arrives from the client, so prove it belongs to this
        // customer before it is attached to an order.
        $address = $user->addresses()->whereKey($params['address_id'])->first();

        if (! $address) {
            throw new CheckoutException('Alamat pengiriman tidak valid.');
        }

        $lines = $this->priceLines($items);
        $subtotal = (float) array_sum(array_column($lines, 'subtotal'));

        $shippingCost = $this->resolveShippingCost(
            $address,
            $items,
            $params['shipping_courier'],
            $params['shipping_service'],
        );

        [$coupon, $discount] = $this->resolveCoupon($user, $subtotal);

        $total = max(0, $subtotal + $shippingCost - $discount);

        // Order, coupon usage and items are written together so a failure midway
        // cannot leave an order without items or a coupon counted for nothing.
        $order = DB::transaction(function () use ($user, $params, $address, $lines, $coupon, $discount, $subtotal, $shippingCost, $total) {
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'address_id' => $address->id,
                'coupon_id' => $coupon?->id,
                'discount_amount' => $discount,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $params['payment_method'] ?? 'pakasir',
                'shipping_courier' => $params['shipping_courier'],
                'shipping_service' => $params['shipping_service'],
                'notes' => $params['notes'] ?? null,
                // System marker, kept out of the customer-written note.
                'source' => $params['source'] ?? 'web',
            ]);

            if ($coupon) {
                // Claim one use atomically: the WHERE means a coupon that ran out
                // between validation and here matches no rows instead of going
                // over quota.
                $claimed = Coupon::whereKey($coupon->id)
                    ->where(function ($q) {
                        $q->whereNull('usage_limit')
                            ->orWhereColumn('used_count', '<', 'usage_limit');
                    })
                    ->increment('used_count');

                if ($claimed === 0) {
                    throw new CheckoutException('Kuota kupon sudah habis. Silakan hapus kupon dan coba lagi.');
                }
            }

            foreach ($lines as $line) {
                $order->items()->create($line);
            }

            $this->reserveStock($lines);

            return $order;
        });

        $paymentUrl = $this->pakasir->createPaymentUrl($order);

        if (! $paymentUrl) {
            // The order transaction has already committed, so its stock and
            // coupon slot are reserved. Hand both back before discarding the
            // order, or a gateway hiccup would quietly eat the shop's stock.
            DB::transaction(function () use ($order) {
                $this->releaseReservations($order, true);
                $order->delete();
            });

            throw new PaymentGatewayException('Pakasir did not return a payment URL for order '.$order->order_number);
        }

        $this->cart->clear();

        // Queued, non-blocking; a mail failure must never fail the order.
        try {
            $user->notify(new OrderPlacedNotification($order));
        } catch (\Throwable $e) {
            Log::error('OrderPlaced notification failed: '.$e->getMessage());
        }

        return ['order' => $order, 'paymentUrl' => $paymentUrl];
    }

    /**
     * Take the ordered quantities out of stock as part of the order transaction.
     *
     * The `stock >= quantity` guard is what makes this safe under concurrency:
     * two checkouts racing for the last unit both run a conditional UPDATE, and
     * whichever loses matches zero rows instead of driving stock negative. Losing
     * rolls the whole order back, so that customer is turned away at checkout
     * instead of paying for goods the shop cannot send.
     *
     * @param  array<int, array<string, mixed>>  $lines
     *
     * @throws CheckoutException
     */
    protected function reserveStock(array $lines): void
    {
        foreach ($lines as $line) {
            $query = $line['product_variant_id']
                ? ProductVariant::whereKey($line['product_variant_id'])
                : Product::whereKey($line['product_id']);

            $affected = $query->where('stock', '>=', $line['quantity'])
                ->decrement('stock', $line['quantity']);

            if ($affected === 0) {
                $name = $line['product_name'].($line['variant_name'] ? " ({$line['variant_name']})" : '');

                throw new CheckoutException("Stok '{$name}' tidak lagi mencukupi. Silakan sesuaikan jumlah di keranjang.");
            }
        }
    }

    /**
     * Cancel an order and hand back everything it reserved.
     *
     * Every cancellation path goes through here — the admin's two buttons, the
     * 24-hour auto-cancel, and the Biteship webhook — so none of them can forget
     * to return stock, free the coupon slot, or tell a paying customer that a
     * refund is owed.
     *
     * The status flip is a compare-and-swap under a row lock, so two
     * cancellations racing (the admin and a courier webhook at the same moment)
     * can only ever restock once.
     *
     * @param  array<string, mixed>  $updates  extra columns to write alongside the cancellation
     * @param  bool|null  $restock  null decides from the status: goods already
     *                              handed to the courier are not on the shelf to
     *                              give back. Pass true when the parcel has
     *                              physically returned to the shop.
     * @return bool true when this call is the one that cancelled the order
     */
    public function cancelAndRelease(Order $order, array $updates = [], ?bool $restock = null): bool
    {
        $cancelled = DB::transaction(function () use ($order, $updates, $restock) {
            $locked = Order::whereKey($order->getKey())->lockForUpdate()->first();

            if (! $locked || in_array($locked->status, ['cancelled', 'completed'], true)) {
                return false; // Already dead, or finished and no longer cancellable.
            }

            $this->releaseReservations($locked, $restock ?? $locked->status !== 'shipped');

            $locked->update(array_merge(['status' => 'cancelled'], $updates));

            return true;
        });

        $order->refresh();

        // Only the call that actually performed the cancellation notifies, so
        // concurrent cancellations cannot email the customer twice.
        if ($cancelled && $order->needsRefund() && $order->user) {
            try {
                $order->user->notify(new OrderRefundPendingNotification($order));
            } catch (\Throwable $e) {
                Log::error('OrderRefundPending notification failed: '.$e->getMessage());
            }
        }

        return $cancelled;
    }

    /**
     * Put an order's stock and coupon slot back. Caller decides whether the
     * goods are physically returnable; the coupon slot always is.
     *
     * Must run inside a transaction that also settles the order's fate, so the
     * release and the status change cannot come apart.
     */
    protected function releaseReservations(Order $order, bool $restock): void
    {
        if ($restock) {
            foreach ($order->items as $item) {
                $relation = $item->product_variant_id ? $item->variant() : $item->product();
                $relation->increment('stock', $item->quantity);
            }
        }

        // Without this a promo capped at 100 uses is exhausted by 100 orders
        // that were never paid for.
        if ($order->coupon_id) {
            Coupon::whereKey($order->coupon_id)
                ->where('used_count', '>', 0)
                ->decrement('used_count');
        }
    }

    /**
     * Build the order lines, pricing each one from the database. The cart holds
     * a snapshot taken when the item was added, so a price change (or a tampered
     * session) must not decide what the customer is charged.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function priceLines(array $items): array
    {
        $productIds = array_values(array_unique(array_column($items, 'product_id')));
        $products = Product::with('variants')->whereIn('id', $productIds)->get()->keyBy('id');

        $lines = [];

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);

            if (! $product) {
                throw new CheckoutException("Produk '{$item['name']}' sudah tidak tersedia.");
            }

            $variant = null;
            if (! empty($item['variant_id'])) {
                $variant = $product->variants->firstWhere('id', $item['variant_id']);

                if (! $variant) {
                    throw new CheckoutException("Variasi untuk '{$product->name}' sudah tidak tersedia.");
                }
            }

            // A variant price replaces the base price; blank means "same as base".
            $price = $variant && $variant->price > 0
                ? (float) $variant->price
                : (float) $product->price;

            $quantity = max(1, (int) $item['quantity']);

            $lines[] = [
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'product_name' => $product->name,
                'variant_name' => $variant?->name,
                'product_price' => $price,
                'quantity' => $quantity,
                'subtotal' => $price * $quantity,
            ];
        }

        return $lines;
    }

    /**
     * Re-quote the chosen courier service from Biteship. The cost posted by the
     * browser is ignored: without this a customer could send shipping_cost=0 and
     * the shop would still pay the real courier fee.
     */
    protected function resolveShippingCost(Address $address, array $items, string $courier, string $service): float
    {
        if (empty($address->area_id)) {
            throw new CheckoutException('Alamat pengiriman belum lengkap (area belum dipilih). Silakan perbarui alamat Anda.');
        }

        $rates = $this->biteship->getShippingRates(
            $address->area_id,
            $items,
            null,
            $address->latitude ? (float) $address->latitude : null,
            $address->longitude ? (float) $address->longitude : null,
        );

        foreach ($rates as $rate) {
            if (($rate['courier_code'] ?? null) === $courier
                && ($rate['courier_service_code'] ?? null) === $service) {
                return (float) ($rate['price'] ?? 0);
            }
        }

        throw new CheckoutException('Pilihan pengiriman sudah tidak tersedia. Silakan pilih ulang kurir dan layanan pengiriman.');
    }

    /**
     * Re-check the applied coupon against the database. The cart only keeps a
     * snapshot from when it was applied, so a coupon that has since expired, been
     * switched off, or run out of quota would otherwise still discount the order.
     *
     * @return array{0: ?Coupon, 1: float}
     */
    protected function resolveCoupon(User $user, float $subtotal): array
    {
        $applied = $this->cart->getCoupon();

        if (! $applied) {
            return [null, 0.0];
        }

        $coupon = Coupon::find($applied['id'] ?? null);

        if (! $coupon || ! $coupon->isValid()) {
            throw new CheckoutException('Kupon sudah tidak berlaku. Silakan hapus kupon dan coba lagi.');
        }

        if ($subtotal < (float) $coupon->min_purchase) {
            throw new CheckoutException('Total belanja belum memenuhi minimal pemakaian kupon ini.');
        }

        // usage_limit caps redemptions overall; this caps them per customer, so a
        // single person cannot spend the same promo on every order.
        if ($coupon->usage_limit_per_user !== null) {
            $usedByCustomer = Order::where('user_id', $user->id)
                ->where('coupon_id', $coupon->id)
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($usedByCustomer >= $coupon->usage_limit_per_user) {
                throw new CheckoutException('Anda sudah mencapai batas pemakaian kupon ini.');
            }
        }

        $discount = $coupon->type === 'percent'
            ? ($subtotal * (float) $coupon->value) / 100
            : min((float) $coupon->value, $subtotal);

        return [$coupon, (float) $discount];
    }
}

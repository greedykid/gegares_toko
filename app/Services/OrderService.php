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
     * @param  array{address_id:int, shipping_courier:string, shipping_service:string, payment_method?:string, notes?:string|null, expected_shipping_cost?:float|null}  $params
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
            isset($params['expected_shipping_cost']) ? (float) $params['expected_shipping_cost'] : null,
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
                // This order is about to take its stock off the shelf a few lines
                // down. Both happen in one transaction, so the marker is never
                // set for stock that was not actually taken.
                'stock_reserved_at' => now(),
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
        // Take the rows in one fixed order, never in cart order. Two carts
        // holding the same two products in opposite order would otherwise each
        // hold the row the other is waiting for — a deadlock on any engine with
        // row-level locking.
        usort($lines, function (array $a, array $b) {
            return [$a['product_variant_id'] ? 1 : 0, $a['product_variant_id'] ?? $a['product_id']]
                <=> [$b['product_variant_id'] ? 1 : 0, $b['product_variant_id'] ?? $b['product_id']];
        });

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
     * Apply a status reported by the courier.
     *
     * The webhook, the ten-minute biteship:sync command and the admin's tracking
     * modal all learn the same thing from Biteship, and each used to translate
     * and write the status itself. Two of them skipped both the transition rules
     * and the cancellation path, so a parcel cancelled at the courier could be
     * marked cancelled while its stock stayed reserved — and because the order
     * was cancelled by then, no later cancellation could ever release it.
     *
     * @param  string  $source  where the report came from, for the log trail
     * @return bool true when the order's status actually changed
     */
    public function applyCourierStatus(Order $order, ?string $courierStatus, string $source = 'Biteship'): bool
    {
        $newStatus = Order::mapCourierStatus($courierStatus);

        if ($newStatus === null || $newStatus === $order->status) {
            return false;
        }

        // The courier's view is not allowed to rewrite history: a delivery
        // notification that arrives late must not drag a completed order back
        // to "shipped".
        if (! $order->canTransitionTo($newStatus)) {
            Log::warning("{$source}: refused status '{$courierStatus}' for Order #{$order->order_number} — cannot move from '{$order->status}' to '{$newStatus}'.");

            return false;
        }

        if ($newStatus === 'cancelled') {
            // Never a bare status write: cancelling has to return the stock and
            // the coupon slot, and tell a paying customer a refund is coming.
            return $this->cancelAndRelease($order, [
                'admin_note' => ($order->admin_note ? $order->admin_note.' | ' : '')
                    ."Dibatalkan otomatis dari Biteship (status: {$courierStatus}).",
            ], Order::courierStatusReturnsGoods($courierStatus) ?: null);
        }

        $order->update(['status' => $newStatus]);
        Log::info("{$source}: Order #{$order->order_number} status updated to {$newStatus}.");

        return true;
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
        // Only give back what this order actually took. Orders written before
        // stock was reserved at checkout carry no marker: they took nothing, and
        // restocking them would invent units that never left the shelf. Clearing
        // the marker as part of the release is also what makes a second release
        // a no-op, rather than trusting every caller to check the status first.
        if ($order->stock_reserved_at !== null) {
            if ($restock) {
                foreach ($order->items as $item) {
                    $relation = $item->product_variant_id ? $item->variant() : $item->product();
                    $relation->increment('stock', $item->quantity);
                }
            }

            $order->stock_reserved_at = null;
            $order->save();
        }

        // The coupon is claimed for every order regardless of when it was made,
        // so this one needs no marker.
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
     * browser never decides what is charged: without this a customer could send
     * shipping_cost=0 and the shop would still pay the real courier fee.
     *
     * It is still worth knowing what the customer was shown, though. Rates are
     * cached for five minutes, so a checkout page left open longer than that can
     * be quoted a different price on submit — and charging more than the total
     * someone agreed to, without a word, is not something to do quietly. A
     * higher quote stops the order so the customer sees the new figure first; a
     * lower one just goes through in their favour.
     *
     * @param  float|null  $expected  what the browser displayed, for comparison only
     */
    protected function resolveShippingCost(
        Address $address,
        array $items,
        string $courier,
        string $service,
        ?float $expected = null,
    ): float {
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
                $price = (float) ($rate['price'] ?? 0);

                if ($expected !== null && $price > $expected) {
                    throw new CheckoutException(
                        'Ongkos kirim berubah dari Rp '.number_format($expected, 0, ',', '.').
                        ' menjadi Rp '.number_format($price, 0, ',', '.').
                        '. Silakan periksa kembali total pesanan Anda sebelum melanjutkan.'
                    );
                }

                return $price;
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

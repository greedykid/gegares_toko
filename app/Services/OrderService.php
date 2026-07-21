<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Exceptions\PaymentGatewayException;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
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
     *                           stale shipping option, invalid coupon, ...).
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

        [$coupon, $discount] = $this->resolveCoupon($subtotal);

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

            return $order;
        });

        $paymentUrl = $this->pakasir->createPaymentUrl($order);

        if (! $paymentUrl) {
            $order->delete();
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
    protected function resolveCoupon(float $subtotal): array
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

        $discount = $coupon->type === 'percent'
            ? ($subtotal * (float) $coupon->value) / 100
            : min((float) $coupon->value, $subtotal);

        return [$coupon, (float) $discount];
    }
}

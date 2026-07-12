<?php

namespace App\Services;

use App\Exceptions\PaymentGatewayException;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Single owner of "turn the current cart into an order".
 *
 * Both the web checkout (CheckoutController) and the AI chatbot
 * (Chatbot::placeDirectOrder) used to duplicate this logic — creating the
 * order, incrementing the coupon, writing items, requesting a Pakasir payment
 * URL, clearing the cart and notifying the customer. Centralising it here keeps
 * the two entry points in lockstep and makes the order+items+coupon write
 * atomic for the chatbot too (previously it was not wrapped in a transaction).
 */
class OrderService
{
    public function __construct(
        protected CartService $cart,
        protected PakasirService $pakasir,
    ) {}

    /**
     * Create an order from the current cart, generate its payment URL, clear the
     * cart and notify the customer.
     *
     * @param  array{address_id:int, shipping_courier:string, shipping_service:string, shipping_cost:float|int, payment_method?:string, notes?:string|null}  $params
     * @return array{order: Order, paymentUrl: string}
     *
     * @throws PaymentGatewayException when the gateway cannot issue a payment
     *                                 link. The half-written order is rolled back before throwing.
     */
    public function createFromCart(User $user, array $params): array
    {
        $items = $this->cart->getItems();
        $coupon = $this->cart->getCoupon();
        $subtotal = $this->cart->getSubtotal();
        $discount = $this->cart->getDiscountAmount();
        $shippingCost = (float) $params['shipping_cost'];
        $total = $subtotal + $shippingCost - $discount;

        // Order, coupon usage and items are written together so a failure midway
        // cannot leave an order without items or a coupon counted for nothing.
        $order = DB::transaction(function () use ($user, $params, $items, $coupon, $discount, $subtotal, $shippingCost, $total) {
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'address_id' => $params['address_id'],
                'coupon_id' => $coupon['id'] ?? null,
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
                Coupon::where('id', $coupon['id'])->increment('used_count');
            }

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'product_name' => $item['name'],
                    'variant_name' => $item['variant_name'] ?? null,
                    'product_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
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
}

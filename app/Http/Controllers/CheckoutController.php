<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\PakasirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index(CartService $cartService)
    {
        $cartItems = $cartService->getItems();

        if (empty($cartItems)) {
            return redirect()->route('products.index')
                ->with('error', 'Keranjang Anda kosong.');
        }

        $errors = $cartService->validateStock();
        if (!empty($errors)) {
            return redirect()->back()
                ->with('warning', implode(' ', $errors));
        }

        $subtotal = $cartService->getSubtotal();
        $discountAmount = $cartService->getDiscountAmount();
        $coupon = $cartService->getCoupon();
        $addresses = auth()->user()->addresses()->orderByDesc('is_primary')->get();

        return view('checkout.index', compact('cartItems', 'subtotal', 'discountAmount', 'coupon', 'addresses'));
    }

    public function store(Request $request, CartService $cartService, PakasirService $pakasirService)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'shipping_courier' => 'required|string',
            'shipping_service' => 'required|string',
            'shipping_cost' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:pakasir',
            'notes' => 'nullable|string',
        ]);
        
        $addressId = $request->address_id;

        $cartItems = $cartService->getItems();
        if (empty($cartItems)) {
            return back()->with('error', 'Keranjang kosong.');
        }

        $errors = $cartService->validateStock();
        if (!empty($errors)) {
            return back()->with('error', implode(' ', $errors));
        }

        $subtotal = $cartService->getSubtotal();
        $shippingCost = $request->shipping_cost;
        $coupon = $cartService->getCoupon();
        $discountAmount = $cartService->getDiscountAmount();
        
        $total = $subtotal + $shippingCost - $discountAmount;

        // Create the order and its items atomically so a failure midway cannot
        // leave a half-written order (e.g. order without items, or a coupon
        // counted twice).
        $order = DB::transaction(function () use ($request, $addressId, $coupon, $discountAmount, $subtotal, $shippingCost, $total, $cartItems) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => Order::generateOrderNumber(),
                'address_id' => $addressId,
                'coupon_id' => $coupon['id'] ?? null,
                'discount_amount' => $discountAmount,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $request->payment_method,
                'shipping_courier' => $request->shipping_courier,
                'shipping_service' => $request->shipping_service,
                'notes' => $request->notes,
            ]);

            if ($coupon) {
                \App\Models\Coupon::where('id', $coupon['id'])->increment('used_count');
            }

            foreach ($cartItems as $item) {
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

        $paymentUrl = $pakasirService->createPaymentUrl($order);

        if (!$paymentUrl) {
            $order->delete();
            return back()->with('error', 'Gagal membuat transaksi pembayaran Pakasir. Silakan coba lagi.');
        }

        $cartService->clear();

        // Notify the customer their order was received (queued, non-blocking).
        try {
            $order->user->notify(new \App\Notifications\OrderPlacedNotification($order));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('OrderPlaced notification failed: ' . $e->getMessage());
        }

        return redirect()->route('orders.payment', $order);
    }
}

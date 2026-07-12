<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentGatewayException;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;

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
        if (! empty($errors)) {
            return redirect()->back()
                ->with('warning', implode(' ', $errors));
        }

        $subtotal = $cartService->getSubtotal();
        $discountAmount = $cartService->getDiscountAmount();
        $coupon = $cartService->getCoupon();
        $addresses = auth()->user()->addresses()->orderByDesc('is_primary')->get();

        return view('checkout.index', compact('cartItems', 'subtotal', 'discountAmount', 'coupon', 'addresses'));
    }

    public function store(Request $request, CartService $cartService, OrderService $orderService)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'shipping_courier' => 'required|string',
            'shipping_service' => 'required|string',
            'shipping_cost' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:pakasir',
            'notes' => 'nullable|string',
        ]);

        if (empty($cartService->getItems())) {
            return back()->with('error', 'Keranjang kosong.');
        }

        $errors = $cartService->validateStock();
        if (! empty($errors)) {
            return back()->with('error', implode(' ', $errors));
        }

        try {
            ['order' => $order] = $orderService->createFromCart(auth()->user(), [
                'address_id' => $request->address_id,
                'shipping_courier' => $request->shipping_courier,
                'shipping_service' => $request->shipping_service,
                'shipping_cost' => $request->shipping_cost,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);
        } catch (PaymentGatewayException $e) {
            return back()->with('error', 'Gagal membuat transaksi pembayaran Pakasir. Silakan coba lagi.');
        }

        return redirect()->route('orders.payment', $order);
    }
}

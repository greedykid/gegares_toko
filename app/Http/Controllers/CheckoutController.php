<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Exceptions\PaymentGatewayException;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            // Scoped to the signed-in customer: an unscoped exists() would let
            // anyone attach someone else's address (and read it back on the
            // order page).
            'address_id' => [
                'required',
                Rule::exists('addresses', 'id')
                    ->where('user_id', auth()->id())
                    ->whereNull('deleted_at'),
            ],
            'shipping_courier' => 'required|string',
            'shipping_service' => 'required|string',
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
            // No shipping_cost is passed: OrderService re-quotes it from Biteship.
            ['order' => $order] = $orderService->createFromCart(auth()->user(), [
                'address_id' => $request->address_id,
                'shipping_courier' => $request->shipping_courier,
                'shipping_service' => $request->shipping_service,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);
        } catch (CheckoutException $e) {
            return back()->with('error', $e->getMessage());
        } catch (PaymentGatewayException $e) {
            return back()->with('error', 'Gagal membuat transaksi pembayaran Pakasir. Silakan coba lagi.');
        }

        return redirect()->route('orders.payment', $order);
    }
}

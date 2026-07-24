<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Exceptions\PaymentGatewayException;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\OrderService;
use App\Support\StoreSchedule;
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

        // Guests have no saved addresses; the checkout view swaps in the
        // session-backed guest address form instead of the DB address list.
        $addresses = auth()->check()
            ? auth()->user()->addresses()->orderByDesc('is_primary')->get()
            : collect();

        // Ordering while the shop is shut is allowed, but not silently: the
        // parcel cannot be collected until someone is here to hand it over, so
        // the page warns and asks for confirmation before taking the money.
        $storeOpen = StoreSchedule::isOpenNow();
        $storeOpensAt = StoreSchedule::nextOpening();

        return view('checkout.index', compact(
            'cartItems', 'subtotal', 'discountAmount', 'coupon', 'addresses',
            'storeOpen', 'storeOpensAt'
        ));
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
            // Not the price we charge — the page re-quotes server-side. This is
            // only what the customer was shown, so a rate that moved before they
            // submitted can be pointed out instead of silently billed.
            'shipping_cost' => 'nullable|numeric|min:0',
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
                'expected_shipping_cost' => $request->shipping_cost,
            ]);
        } catch (CheckoutException $e) {
            return back()->with('error', $e->getMessage());
        } catch (PaymentGatewayException $e) {
            return back()->with('error', 'Gagal membuat transaksi pembayaran Pakasir. Silakan coba lagi.');
        }

        return redirect()->route('orders.payment', $order);
    }

    /**
     * A guest finished the checkout steps. There is no account to place the
     * order against yet, so stash everything in the session and send them to log
     * in or register. The intended URL brings them straight back to resume().
     */
    public function guestSubmit(Request $request, CartService $cartService)
    {
        $request->validate([
            'shipping_courier' => 'required|string',
            'shipping_service' => 'required|string',
            'payment_method' => 'required|string|in:pakasir',
            'notes' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);

        if (empty($cartService->getItems())) {
            return redirect()->route('products.index')->with('error', 'Keranjang Anda kosong.');
        }

        $errors = $cartService->validateStock();
        if (! empty($errors)) {
            return back()->with('error', implode(' ', $errors));
        }

        // The guest must have completed the address step first.
        if (empty(session('checkout.guest_address.area_id'))) {
            return back()->with('error', 'Lengkapi alamat pengiriman terlebih dahulu.');
        }

        session(['checkout.pending' => [
            'shipping_courier' => $request->shipping_courier,
            'shipping_service' => $request->shipping_service,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'shipping_cost' => $request->shipping_cost,
        ]]);

        // After login/register, redirect()->intended() lands here on resume().
        session(['url.intended' => route('checkout.resume')]);

        return redirect()->route('login')
            ->with('info', 'Masuk atau daftar untuk menyelesaikan pesanan Anda.');
    }

    /**
     * Runs right after a guest authenticates (auth + check_phone middleware).
     * Materialise the session address into a real Address, then place the order
     * exactly as store() would.
     */
    public function resume(CartService $cartService, OrderService $orderService, AddressService $addressService)
    {
        // An admin is not a shopper: never turn their login into a customer order,
        // even if a guest's pending checkout is still sitting in this session.
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $pending = session('checkout.pending');
        $guestAddress = session('checkout.guest_address');

        if (empty($pending) || empty($guestAddress['area_id'])) {
            return redirect()->route('checkout.index');
        }

        // A brand-new account (e.g. via Google) may have no phone yet. Send them
        // to complete it, re-arming the intended URL so they come back here.
        if (! auth()->user()->phone) {
            session(['url.intended' => route('checkout.resume')]);

            return redirect()->route('settings.complete-profile');
        }

        if (empty($cartService->getItems())) {
            return redirect()->route('products.index')->with('error', 'Keranjang Anda kosong.');
        }

        $errors = $cartService->validateStock();
        if (! empty($errors)) {
            return redirect()->route('checkout.index')->with('warning', implode(' ', $errors));
        }

        try {
            $address = $addressService->create(auth()->user(), $guestAddress);

            ['order' => $order] = $orderService->createFromCart(auth()->user(), [
                'address_id' => $address->id,
                'shipping_courier' => $pending['shipping_courier'],
                'shipping_service' => $pending['shipping_service'],
                'payment_method' => $pending['payment_method'] ?? 'pakasir',
                'notes' => $pending['notes'] ?? null,
                'expected_shipping_cost' => $pending['shipping_cost'] ?? null,
            ]);
        } catch (CheckoutException $e) {
            return redirect()->route('checkout.index')->with('error', $e->getMessage());
        } catch (PaymentGatewayException $e) {
            return redirect()->route('checkout.index')
                ->with('error', 'Gagal membuat transaksi pembayaran Pakasir. Silakan coba lagi.');
        }

        session()->forget(['checkout.pending', 'checkout.guest_address']);

        return redirect()->route('orders.payment', $order);
    }
}

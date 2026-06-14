<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;
use Livewire\Attributes\On;

class CartDrawer extends Component
{
    public bool $open = false;
    public array $items = [];
    public float $subtotal = 0;
    public string $couponCode = '';
    public ?array $coupon = null;
    public float $discount = 0;
    public float $total = 0;

    public function mount(): void
    {
        $this->refreshCart();
    }

    #[On('toggle-cart')]
    public function toggle(): void
    {
        $this->open = !$this->open;
        $this->refreshCart();
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        $cartService = app(CartService::class);
        $this->items = $cartService->getItems();
        $this->subtotal = $cartService->getSubtotal();
        $this->coupon = $cartService->getCoupon();
        $this->discount = $cartService->getDiscountAmount();
        $this->total = $cartService->getTotal();
    }

    public function applyCoupon(): void
    {
        $this->validate([
            'couponCode' => 'required|string|min:3',
        ], [
            'couponCode.required' => 'Masukkan kode kupon.',
            'couponCode.min' => 'Kode kupon terlalu pendek.',
        ]);

        $cartService = app(CartService::class);
        $result = $cartService->applyCoupon($this->couponCode);

        if ($result['success']) {
            $this->couponCode = '';
            $this->dispatch('toast', type: 'success', message: $result['message']);
        } else {
            $this->dispatch('toast', type: 'error', message: $result['message']);
        }

        $this->refreshCart();
    }

    public function removeCoupon(): void
    {
        $cartService = app(CartService::class);
        $cartService->removeCoupon();
        $this->refreshCart();
        $this->dispatch('toast', type: 'success', message: 'Kupon berhasil dihapus.');
    }

    public function updateQuantity(string $cartKey, mixed $quantity): void
    {
        $quantity = (int) $quantity;
        $cartService = app(CartService::class);
        $itemBefore = $this->items[$cartKey] ?? null;

        $cartService->update($cartKey, $quantity);
        $this->refreshCart();
        $this->dispatch('cart-updated');

        if ($quantity <= 0) {
            $this->dispatch('toast', type: 'success', message: 'Produk dihapus dari keranjang.');
        } elseif ($itemBefore && $quantity > $itemBefore['stock']) {
            $this->dispatch('toast', type: 'info', message: 'Jumlah maksimal disesuaikan dengan stok yang ada.');
        }
    }

    public function removeItem(string $cartKey): void
    {
        $cartService = app(CartService::class);
        $cartService->remove($cartKey);
        $this->refreshCart();
        $this->dispatch('cart-updated');
        $this->dispatch('toast', type: 'success', message: 'Produk dihapus dari keranjang.');
    }

    public function render()
    {
        return view('livewire.cart-drawer');
    }
}

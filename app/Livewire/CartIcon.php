<?php

namespace App\Livewire;

use App\Services\CartService;
use App\Models\Wishlist;
use Livewire\Component;
use Livewire\Attributes\On;

class CartIcon extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->updateCount();
    }

    #[On('cart-updated')]
    public function updateCount(): void
    {
        $this->count = app(CartService::class)->getCount();
    }

    #[On('add-to-cart')]
    public function addToCart(int $productId): void
    {
        $cartService = app(CartService::class);
        $result = $cartService->add($productId);
        
        if ($result['success'] && auth()->check()) {
            Wishlist::where('user_id', auth()->id())
                ->where('product_id', $productId)
                ->delete();
            $this->dispatch('wishlist-updated');
        }

        $this->updateCount();

        $this->dispatch('toast', type: $result['success'] ? 'success' : 'error', message: $result['message']);
        $this->dispatch('cart-updated');
    }

    #[On('add-to-cart-qty')]
    public function addToCartQty(int $productId, int $quantity = 1, ?int $variantId = null): void
    {
        $cartService = app(CartService::class);
        $result = $cartService->add($productId, $quantity, $variantId);
        
        if ($result['success'] && auth()->check()) {
            Wishlist::where('user_id', auth()->id())
                ->where('product_id', $productId)
                ->delete();
            $this->dispatch('wishlist-updated');
        }

        $this->updateCount();

        $this->dispatch('toast', type: $result['success'] ? 'success' : 'error', message: $result['message']);
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.cart-icon');
    }
}

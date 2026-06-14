<?php

namespace App\Livewire;

use App\Models\Wishlist;
use App\Services\CartService;
use Livewire\Component;
use Livewire\Attributes\On;

class WishlistDrawer extends Component
{
    public bool $open = false;
    public $items = [];

    public function mount(): void
    {
        $this->refreshWishlist();
    }

    #[On('toggle-wishlist')]
    public function toggle(): void
    {
        $this->open = !$this->open;
        if ($this->open) {
            $this->refreshWishlist();
        }
    }

    #[On('wishlist-updated')]
    public function refreshWishlist(): void
    {
        if (auth()->check()) {
            $this->items = auth()->user()->wishlists()
                ->with('product')
                ->latest()
                ->get();
        } else {
            $this->items = [];
        }
    }

    public function removeItem(int $wishlistId): void
    {
        $wishlist = auth()->user()->wishlists()->find($wishlistId);
        
        if ($wishlist) {
            $wishlist->delete();
            $this->refreshWishlist();
            $this->dispatch('wishlist-updated');
            $this->dispatch('toast', type: 'success', message: 'Produk dihapus dari wishlist.');
        }
    }

    public function addToCart(int $productId, int $wishlistId): void
    {
        $cartService = app(CartService::class);
        $result = $cartService->add($productId);
        
        if ($result['success']) {
            // Optional: Remove from wishlist after adding to cart
            // $this->removeItem($wishlistId);
            
            $this->dispatch('cart-updated');
            $this->dispatch('toast', type: 'success', message: $result['message']);
        } else {
            $this->dispatch('toast', type: 'error', message: $result['message']);
        }
    }

    public function render()
    {
        return view('livewire.wishlist-drawer');
    }
}

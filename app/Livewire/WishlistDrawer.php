<?php

namespace App\Livewire;

use App\Models\Product;
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
            // whereHas('product') hides rows whose product has been soft-deleted,
            // so the drawer and the navbar badge count the same thing. withCount
            // lets the view decide "add to cart" vs "pick a variant" without a
            // query per row.
            $this->items = auth()->user()->wishlists()
                ->whereHas('product')
                ->with(['product' => fn ($q) => $q->withCount('variants')])
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
        $product = Product::withCount('variants')->find($productId);

        if (! $product) {
            $this->dispatch('toast', type: 'error', message: 'Produk tidak ditemukan.');

            return;
        }

        // The wishlist only remembers the product, not which portion/variant.
        // Adding one blindly would charge the base price or be refused when the
        // stock lives on a variant — so send the customer to the page to choose.
        if ($product->hasVariants()) {
            $this->redirectRoute('products.show', ['product' => $product]);

            return;
        }

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

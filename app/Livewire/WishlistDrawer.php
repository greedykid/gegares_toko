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
        if (session()->has('open_wishlist_drawer')) {
            $this->open = (bool) session()->pull('open_wishlist_drawer');
        }
        $this->refreshWishlist();
    }

    public function updatedOpen($value): void
    {
        if ($value) {
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
                ->with(['product' => fn ($q) => $q->withCount('variants')->with('category')])
                ->latest()
                ->get();
        } else {
            $this->items = [];
        }
    }

    public function removeItem(int $wishlistId): void
    {
        if ($this->deleteWishlistItem($wishlistId)) {
            $this->refreshWishlist();
            $this->dispatch('wishlist-updated');
            $this->dispatch('toast', type: 'success', message: 'Produk dihapus dari wishlist.');
        }
    }

    /**
     * Drop the row without announcing it. addToCart() reuses this so moving a
     * product to the cart stays one action with one toast, instead of stacking
     * "added to cart" on top of "removed from wishlist".
     */
    private function deleteWishlistItem(int $wishlistId): bool
    {
        $wishlist = auth()->user()->wishlists()->find($wishlistId);

        if (! $wishlist) {
            return false;
        }

        $wishlist->delete();

        return true;
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
            // The product is in the cart now, so keeping it in the wishlist would
            // just pile up already-bought items. Only on success — a rejected add
            // (out of stock, unavailable) must leave the wishlist untouched.
            $this->deleteWishlistItem($wishlistId);
            $this->refreshWishlist();

            $this->dispatch('cart-updated');
            $this->dispatch('wishlist-updated');
            $this->dispatch('toast', type: 'success', message: "{$product->name} dipindahkan ke keranjang.");
        } else {
            $this->dispatch('toast', type: 'error', message: $result['message']);
        }
    }

    public function render()
    {
        return view('livewire.wishlist-drawer');
    }
}

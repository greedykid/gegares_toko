<?php

namespace App\Livewire;

use App\Models\Wishlist;
use Livewire\Component;

class ToggleWishlist extends Component
{
    public int $productId;
    public bool $isWishlisted = false;
    public string $variant = 'icon'; // 'icon' or 'button'

    public function mount(int $productId, string $variant = 'icon'): void
    {
        $this->productId = $productId;
        $this->variant = $variant;
        $this->checkWishlistStatus();
    }

    public function checkWishlistStatus(): void
    {
        if (auth()->check()) {
            $this->isWishlisted = Wishlist::where('user_id', auth()->id())
                ->where('product_id', $this->productId)
                ->exists();
        }
    }

    public function toggle(): void
    {
        if (!auth()->check()) {
            session(['pending_wishlist_product_id' => $this->productId]);
            $this->redirectRoute('login');
            return;
        }

        if ($this->isWishlisted) {
            Wishlist::where('user_id', auth()->id())
                ->where('product_id', $this->productId)
                ->delete();
            $this->isWishlisted = false;
            
            $productName = \App\Models\Product::find($this->productId)?->name ?? 'Produk';
            $this->dispatch('toast', type: 'info', message: "{$productName} dihapus dari wishlist");
        } else {
            // firstOrCreate, not create: the button decides from this component's
            // cached isWishlisted, so two toggles racing (or a second component
            // for the same product) could both take the "add" path. The unique
            // (user_id, product_id) index already forbids a duplicate row — this
            // keeps that from surfacing as a QueryException.
            Wishlist::firstOrCreate([
                'user_id' => auth()->id(),
                'product_id' => $this->productId,
            ]);
            $this->isWishlisted = true;
            
            $productName = \App\Models\Product::find($this->productId)?->name ?? 'Produk';
            $this->dispatch('toast', type: 'success', message: "{$productName} ditambahkan ke wishlist");
        }

        $this->dispatch('wishlist-updated');
    }

    public function render()
    {
        return view('livewire.toggle-wishlist');
    }
}

<?php

namespace App\Traits;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

trait HandlesPendingWishlist
{
    /**
     * Process any wishlist item saved while guest, add to DB, and set session flags to open drawer & show toast.
     */
    protected function handlePendingWishlist(): void
    {
        if (session()->has('pending_wishlist_product_id')) {
            $productId = (int) session()->pull('pending_wishlist_product_id');
            if ($productId && Auth::check()) {
                Wishlist::firstOrCreate([
                    'user_id' => Auth::id(),
                    'product_id' => $productId,
                ]);

                $productName = Product::find($productId)?->name ?? 'Produk';
                session()->flash('success', "{$productName} ditambahkan ke wishlist");
                session()->put('open_wishlist_drawer', true);
            }
        }
    }
}

<?php

namespace App\Livewire;

use App\Models\Wishlist;
use Livewire\Component;
use Livewire\Attributes\On;

class WishlistIcon extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->updateCount();
    }

    #[On('wishlist-updated')]
    public function updateCount(): void
    {
        // whereHas('product') so a soft-deleted product does not inflate the
        // badge past what the drawer actually shows.
        $this->count = auth()->check()
            ? Wishlist::where('user_id', auth()->id())->whereHas('product')->count()
            : 0;
    }

    public function render()
    {
        return view('livewire.wishlist-icon');
    }
}

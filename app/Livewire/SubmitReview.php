<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\ImageOptimizer;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class SubmitReview extends Component
{
    use WithFileUploads;

    /**
     * Locked: plain public properties are client-writable by design (that is how
     * wire:model works), so without this the browser could point the component at
     * any order or product. The Blade guard around this component only controls
     * what is rendered, it does not survive a later component update.
     */
    #[Locked]
    public int $orderId;

    #[Locked]
    public int $productId;

    public int $rating = 5;

    public string $comment = '';

    public $image;

    public bool $isSubmitted = false;

    public bool $canReview = false;

    public ?Review $existingReview = null;

    public function mount(int $orderId, int $productId)
    {
        $this->orderId = $orderId;
        $this->productId = $productId;

        $this->canReview = $this->eligibleOrder() !== null;

        // Scoped to this customer: the eligibility check already ties the order
        // to them, and this keeps one person's review from occupying another's slot.
        $this->existingReview = Review::where('order_id', $this->orderId)
            ->where('product_id', $this->productId)
            ->where('user_id', Auth::id())
            ->first();

        $this->isSubmitted = (bool) $this->existingReview;
    }

    /**
     * The order this review may be attached to. It must belong to the signed-in
     * customer, have been delivered, and actually contain the product — a review
     * is a statement about something they received.
     */
    protected function eligibleOrder(): ?Order
    {
        if (! Auth::check()) {
            return null;
        }

        return Order::whereKey($this->orderId)
            ->where('user_id', Auth::id())
            ->where('status', 'completed')
            ->whereHas('items', fn ($q) => $q->where('product_id', $this->productId))
            ->first();
    }

    public function submit()
    {
        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:2048', // 2MB max
        ]);

        if ($this->isSubmitted) {
            return;
        }

        // Re-checked here, not just on mount: the order could have changed state
        // (or this could be a crafted request) since the component was rendered.
        if (! $this->eligibleOrder()) {
            $this->canReview = false;
            $this->dispatch('toast', type: 'error', message: 'Ulasan hanya bisa dikirim untuk produk pada pesanan Anda yang sudah selesai.');

            return;
        }

        $imagePath = null;
        if ($this->image) {
            $imagePath = app(ImageOptimizer::class)->store($this->image, 'reviews');
        }

        $this->existingReview = Review::create([
            'user_id' => Auth::id(),
            'order_id' => $this->orderId,
            'product_id' => $this->productId,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'image' => $imagePath,
            // Published immediately by choice; the admin moderates after the fact
            // and can unpublish or delete from the reviews screen, which
            // recalculates the product rating.
            'is_approved' => true,
        ]);

        $this->isSubmitted = true;

        // Update product average rating
        $product = Product::find($this->productId);
        if ($product) {
            $product->updateRating();
        }

        $this->dispatch('toast', type: 'success', message: 'Terima kasih! Ulasan Anda berhasil dikirim.');
    }

    public function render()
    {
        return view('livewire.submit-review');
    }
}

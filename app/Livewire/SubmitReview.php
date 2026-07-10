<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\ImageOptimizer;
use Livewire\Component;
use Livewire\WithFileUploads;

class SubmitReview extends Component
{
    use WithFileUploads;

    public int $orderId;
    public int $productId;
    
    public int $rating = 5;
    public string $comment = '';
    public $image;
    
    public bool $isSubmitted = false;
    public ?Review $existingReview = null;

    public function mount(int $orderId, int $productId)
    {
        $this->orderId = $orderId;
        $this->productId = $productId;
        
        $this->existingReview = Review::where('order_id', $this->orderId)
            ->where('product_id', $this->productId)
            ->first();

        $this->isSubmitted = (bool)$this->existingReview;
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

        $imagePath = null;
        if ($this->image) {
            $imagePath = app(ImageOptimizer::class)->store($this->image, 'reviews');
        }

        $this->existingReview = Review::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'order_id' => $this->orderId,
            'product_id' => $this->productId,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'image' => $imagePath,
            'is_approved' => true, // Auto approve for display
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

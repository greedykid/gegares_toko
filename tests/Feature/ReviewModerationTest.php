<?php

namespace Tests\Feature;

use App\Livewire\SubmitReview;
use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Support\ContentModeration;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewModerationTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(string $slug = 'klepon'): Product
    {
        $category = Category::firstOrCreate(['slug' => 'snack'], ['name' => 'Snack', 'is_active' => true]);

        return Product::create([
            'category_id' => $category->id,
            'name' => ucfirst($slug),
            'slug' => $slug,
            'price' => 10000,
            'stock' => 50,
        ]);
    }

    private function makeCompletedOrder(User $user, Product $product): Order
    {
        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Rumah',
            'recipient_name' => 'Test User',
            'phone' => '081234567890',
            'address_line' => 'Jl. Tebet Raya No. 1',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12810',
            'is_primary' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'GGR-REVMOD-'.strtoupper(uniqid()),
            'address_id' => $address->id,
            'subtotal' => 10000,
            'shipping_cost' => 9000,
            'total' => 19000,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => 1,
            'subtotal' => 10000,
        ]);

        return $order;
    }

    public function test_profanity_filter_flags_abuse_but_lets_clean_text_through(): void
    {
        $this->assertTrue(ContentModeration::containsProfanity('dasar anjing penjualnya'));
        $this->assertTrue(ContentModeration::containsProfanity('g0bl0k banget'), 'Leet spelling should still be caught.');
        $this->assertFalse(ContentModeration::containsProfanity('Enak sekali, pengiriman cepat!'));
        // Must not fire on an innocent word that merely contains a bad substring.
        $this->assertFalse(ContentModeration::containsProfanity('Assalamualaikum, terima kasih'));
        $this->assertFalse(ContentModeration::containsProfanity(null));
    }

    public function test_a_review_with_profanity_is_held_and_does_not_move_the_rating(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $order = $this->makeCompletedOrder($user, $product);

        $this->actingAs($user);

        Livewire::test(SubmitReview::class, ['orderId' => $order->id, 'productId' => $product->id])
            ->set('rating', 1)
            ->set('comment', 'penjual anjing lama banget')
            ->call('submit')
            ->assertSet('isSubmitted', true);

        $review = Review::where('product_id', $product->id)->first();
        $this->assertNotNull($review);
        $this->assertFalse((bool) $review->is_approved, 'An abusive review must be held for moderation.');

        // Held reviews never count toward the public rating.
        $this->assertEquals(0.0, (float) $product->fresh()->rating_avg);
        $this->assertEquals(0, $product->fresh()->rating_count);
    }

    public function test_a_clean_review_publishes_immediately(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $order = $this->makeCompletedOrder($user, $product);

        $this->actingAs($user);

        Livewire::test(SubmitReview::class, ['orderId' => $order->id, 'productId' => $product->id])
            ->set('rating', 4)
            ->set('comment', 'Enak dan fresh')
            ->call('submit');

        $review = Review::where('product_id', $product->id)->first();
        $this->assertTrue((bool) $review->is_approved);
        $this->assertEquals(4.0, (float) $product->fresh()->rating_avg);
    }

    public function test_a_duplicate_review_is_refused_by_the_unique_index(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $order = $this->makeCompletedOrder($user, $product);

        Review::create([
            'user_id' => $user->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'rating' => 5, 'comment' => 'first', 'is_approved' => true,
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        Review::create([
            'user_id' => $user->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'rating' => 1, 'comment' => 'second', 'is_approved' => true,
        ]);
    }

    public function test_deleting_a_review_removes_its_image_file(): void
    {
        Storage::fake('public');
        $path = 'reviews/photo.jpg';
        Storage::disk('public')->put($path, 'fake-bytes');

        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $product = $this->makeProduct();
        $order = $this->makeCompletedOrder($customer, $product);

        $review = Review::create([
            'user_id' => $customer->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'rating' => 5, 'comment' => 'nice', 'image' => $path, 'is_approved' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.reviews.destroy', $review))
            ->assertRedirect();

        Storage::disk('public')->assertMissing($path);
        $this->assertNull($review->fresh()->image);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }
}

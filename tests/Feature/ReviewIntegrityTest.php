<?php

namespace Tests\Feature;

use App\Livewire\SubmitReview;
use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * A review is a statement about something the customer actually received, so it
 * is only accepted for a delivered order of theirs that contains the product.
 * Two layers enforce that: #[Locked] stops the browser repointing the component,
 * and submit() re-checks eligibility server-side.
 */
class ReviewIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(string $slug): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'snack'],
            ['name' => 'Snack', 'is_active' => true]
        );

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
            'order_number' => 'GGR-REVIEW-'.strtoupper(uniqid()),
            'address_id' => $address->id,
            'subtotal' => 10000,
            'shipping_cost' => 9000,
            'total' => 19000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
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

    public function test_a_legitimate_review_is_still_accepted(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct('klepon');
        $order = $this->makeCompletedOrder($user, $product);

        $this->actingAs($user);

        Livewire::test(SubmitReview::class, ['orderId' => $order->id, 'productId' => $product->id])
            ->assertSet('canReview', true)
            ->set('rating', 5)
            ->set('comment', 'Enak sekali!')
            ->call('submit')
            ->assertSet('isSubmitted', true);

        $this->assertEquals(1, Review::where('product_id', $product->id)->count());
        $this->assertEquals(5.0, (float) $product->fresh()->rating_avg);
    }

    public function test_the_browser_cannot_repoint_the_component_at_another_product(): void
    {
        $user = User::factory()->create();
        $bought = $this->makeProduct('klepon');
        $neverBought = $this->makeProduct('combro');
        $order = $this->makeCompletedOrder($user, $bought);

        $this->actingAs($user);

        try {
            Livewire::test(SubmitReview::class, ['orderId' => $order->id, 'productId' => $bought->id])
                ->set('productId', $neverBought->id)
                ->call('submit');

            $this->fail('A locked property was allowed to change.');
        } catch (CannotUpdateLockedPropertyException $e) {
            // expected: Livewire refuses the update outright
        }

        $this->assertEquals(0, Review::where('product_id', $neverBought->id)->count());
    }

    public function test_a_product_that_is_not_in_the_order_is_refused(): void
    {
        // Second layer: mount crafted directly, bypassing #[Locked] entirely.
        $user = User::factory()->create();
        $bought = $this->makeProduct('klepon');
        $neverBought = $this->makeProduct('combro');
        $order = $this->makeCompletedOrder($user, $bought);

        $this->actingAs($user);

        Livewire::test(SubmitReview::class, ['orderId' => $order->id, 'productId' => $neverBought->id])
            ->assertSet('canReview', false)
            ->call('submit');

        $this->assertEquals(0, Review::where('product_id', $neverBought->id)->count());
    }

    public function test_another_customers_order_is_refused(): void
    {
        $victim = User::factory()->create();
        $product = $this->makeProduct('klepon');
        $victimOrder = $this->makeCompletedOrder($victim, $product);

        $attacker = User::factory()->create();
        $this->actingAs($attacker);

        Livewire::test(SubmitReview::class, ['orderId' => $victimOrder->id, 'productId' => $product->id])
            ->assertSet('canReview', false)
            ->call('submit');

        $this->assertEquals(0, Review::where('order_id', $victimOrder->id)->count());
    }

    public function test_an_order_that_is_not_completed_yet_cannot_be_reviewed(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct('klepon');
        $order = $this->makeCompletedOrder($user, $product);
        $order->update(['status' => 'shipped']); // still in transit

        $this->actingAs($user);

        Livewire::test(SubmitReview::class, ['orderId' => $order->id, 'productId' => $product->id])
            ->assertSet('canReview', false)
            ->call('submit');

        $this->assertEquals(0, Review::count(), 'A review was accepted before delivery.');
    }
}

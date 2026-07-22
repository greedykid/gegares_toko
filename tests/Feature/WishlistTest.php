<?php

namespace Tests\Feature;

use App\Livewire\ToggleWishlist;
use App\Livewire\WishlistDrawer;
use App\Livewire\WishlistIcon;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'gorengan'],
            ['name' => 'Gorengan', 'is_active' => true]
        );

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Bakwan',
            'slug' => 'bakwan-'.uniqid(),
            'price' => 12000,
            'stock' => 50,
        ], $overrides));
    }

    public function test_has_variants_reflects_whether_the_product_has_variants(): void
    {
        $plain = $this->makeProduct();
        $this->assertFalse($plain->hasVariants());

        $withVariant = $this->makeProduct(['name' => 'Risoles', 'slug' => 'risoles']);
        ProductVariant::create([
            'product_id' => $withVariant->id,
            'name' => '1 Porsi',
            'price' => 30000,
            'stock' => 10,
        ]);

        $this->assertTrue($withVariant->fresh()->hasVariants());
    }

    public function test_add_to_cart_adds_a_plain_product(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $wishlist = Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user);

        Livewire::test(WishlistDrawer::class)
            ->call('addToCart', $product->id, $wishlist->id);

        $this->assertArrayHasKey($product->id.'_0', Session::get('cart', []));
    }

    public function test_add_to_cart_redirects_variant_products_to_the_product_page(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(['name' => 'Risoles', 'slug' => 'risoles']);
        ProductVariant::create([
            'product_id' => $product->id,
            'name' => '1 Porsi',
            'price' => 30000,
            'stock' => 10,
        ]);
        $wishlist = Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user);

        Livewire::test(WishlistDrawer::class)
            ->call('addToCart', $product->id, $wishlist->id)
            ->assertRedirect(route('products.show', $product));

        // Nothing was added — the customer still has to pick a portion.
        $this->assertEmpty(Session::get('cart', []));
    }

    public function test_toggling_add_twice_never_throws_or_duplicates(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $this->actingAs($user);

        Livewire::test(ToggleWishlist::class, ['productId' => $product->id])
            ->assertSet('isWishlisted', false)
            ->call('toggle')
            ->assertSet('isWishlisted', true);

        // Simulate a stale second component (or a fast double-click) that still
        // believes the product is not wishlisted and takes the "add" path again.
        // firstOrCreate must swallow the race instead of hitting the unique index.
        Livewire::test(ToggleWishlist::class, ['productId' => $product->id])
            ->set('isWishlisted', false)
            ->call('toggle')
            ->assertSet('isWishlisted', true);

        $this->assertEquals(1, Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->count());
    }

    public function test_soft_deleted_products_are_excluded_from_the_badge_and_the_drawer(): void
    {
        $user = User::factory()->create();
        $live = $this->makeProduct(['name' => 'Bakwan', 'slug' => 'bakwan-live']);
        $gone = $this->makeProduct(['name' => 'Cireng', 'slug' => 'cireng-gone']);
        Wishlist::create(['user_id' => $user->id, 'product_id' => $live->id]);
        Wishlist::create(['user_id' => $user->id, 'product_id' => $gone->id]);

        $gone->delete(); // soft delete — FK cascade does not fire

        $this->actingAs($user);

        // Badge counts only the surviving product...
        Livewire::test(WishlistIcon::class)->assertSet('count', 1);

        // ...and the drawer lists the same one, so the two agree.
        $items = Livewire::test(WishlistDrawer::class)->get('items');
        $this->assertCount(1, $items);
        $this->assertEquals($live->id, $items->first()->product_id);
    }
}

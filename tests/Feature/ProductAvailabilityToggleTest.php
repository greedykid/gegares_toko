<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Hybrid stock model: the admin's Tersedia/Habis switch decides what customers
 * see and may buy, while the numeric stock keeps running underneath.
 */
class ProductAvailabilityToggleTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'snack'],
            ['name' => 'Snack', 'is_active' => true]
        );

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon',
            'price' => 10000.00,
            'stock' => 10,
        ], $overrides));
    }

    public function test_products_are_available_by_default(): void
    {
        $product = $this->makeProduct();

        $this->assertTrue($product->fresh()->is_available);
        $this->assertFalse($product->isOutOfStock());
    }

    public function test_switching_a_product_off_marks_it_out_of_stock_despite_stock(): void
    {
        // Stock still 10, but the shop took it off the menu.
        $product = $this->makeProduct(['is_available' => false]);

        $this->assertTrue($product->isOutOfStock());
        $this->assertFalse($product->isLowStock());
        $this->assertEquals(10, $product->stock, 'The counter must be preserved.');
    }

    public function test_an_unavailable_product_cannot_be_added_to_the_cart(): void
    {
        $product = $this->makeProduct(['is_available' => false]);

        $result = app(CartService::class)->add($product->id, 1);

        $this->assertFalse($result['success']);
        $this->assertEmpty(app(CartService::class)->getItems());
    }

    public function test_validate_stock_drops_a_product_switched_off_mid_session(): void
    {
        $product = $this->makeProduct();
        $cart = app(CartService::class);

        $this->assertTrue($cart->add($product->id, 2)['success']);

        // Admin flips it to "Habis" while the item sits in the cart.
        $product->update(['is_available' => false]);

        $errors = $cart->validateStock();

        $this->assertNotEmpty($errors);
        $this->assertEmpty($cart->getItems());
    }

    public function test_in_stock_scope_excludes_unavailable_products(): void
    {
        $this->makeProduct(['name' => 'Tersedia', 'slug' => 'tersedia']);
        $this->makeProduct(['name' => 'Dimatikan', 'slug' => 'dimatikan', 'is_available' => false]);

        $names = Product::inStock()->pluck('name')->all();

        $this->assertContains('Tersedia', $names);
        $this->assertNotContains('Dimatikan', $names);
    }

    public function test_admin_can_toggle_availability_without_touching_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->makeProduct();

        $this->actingAs($admin)
            ->from(route('admin.products.index'))
            ->patch(route('admin.products.toggle-availability', $product))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $product->refresh();
        $this->assertFalse($product->is_available);
        $this->assertEquals(10, $product->stock);

        // Toggling back restores availability, stock still intact.
        $this->actingAs($admin)->patch(route('admin.products.toggle-availability', $product));

        $product->refresh();
        $this->assertTrue($product->is_available);
        $this->assertEquals(10, $product->stock);
    }
}

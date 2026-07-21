<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A variant's price is the price of that portion, not a surcharge on top of the
 * base price. Leaving it blank means the variant costs the same as the base.
 */
class VariantPricingTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(float $price = 12000): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'gorengan'],
            ['name' => 'Gorengan', 'is_active' => true]
        );

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Bakwan',
            'slug' => 'bakwan',
            'price' => $price,
            'stock' => 50,
        ]);
    }

    public function test_variant_price_replaces_the_base_price(): void
    {
        // Base 12.000, variant 30.000 → cart must show 30.000, not 42.000.
        $product = $this->makeProduct(12000);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '1 Porsi (isi 10)',
            'price' => 30000,
            'stock' => 100,
        ]);

        $cart = app(CartService::class);
        $this->assertTrue($cart->add($product->id, 1, $variant->id)['success']);

        $item = collect($cart->getItems())->first();
        $this->assertEquals(30000, $item['price']);
        $this->assertEquals(30000, $cart->getSubtotal());
    }

    public function test_a_variant_without_a_price_falls_back_to_the_base_price(): void
    {
        $product = $this->makeProduct(12000);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Reguler',
            'price' => null,
            'stock' => 10,
        ]);

        $cart = app(CartService::class);
        $cart->add($product->id, 2, $variant->id);

        $item = collect($cart->getItems())->first();
        $this->assertEquals(12000, $item['price']);
        $this->assertEquals(24000, $cart->getSubtotal());
    }

    public function test_buying_without_a_variant_still_uses_the_base_price(): void
    {
        $product = $this->makeProduct(12000);

        $cart = app(CartService::class);
        $cart->add($product->id, 3);

        $this->assertEquals(36000, $cart->getSubtotal());
    }
}

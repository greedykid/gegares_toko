<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin edit modals build their form action in Alpine. Hardcoding the English
 * path (/admin/products/…) 404s, because the resource URIs are Indonesian
 * (/admin/produk/…) while only the route NAMES stay English.
 */
class AdminEditFormUrlTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function makeCategory(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'gorengan'],
            ['name' => 'Gorengan', 'is_active' => true]
        );
    }

    public function test_product_edit_form_targets_the_indonesian_update_url(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertSee('admin/produk/__SLUG__', false);
        $response->assertDontSee(url('admin/products').'/', false);
    }

    public function test_category_edit_form_targets_the_indonesian_update_url(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));

        $response->assertOk();
        $response->assertSee('admin/kategori/__SLUG__', false);
        $response->assertDontSee(url('admin/categories').'/', false);
    }

    /** The exact flow that was failing: edit a product, add a variant, save. */
    public function test_admin_can_save_a_product_with_a_new_variant(): void
    {
        $category = $this->makeCategory();
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Bakwan',
            'slug' => 'bakwan',
            'price' => 3000.00,
            'stock' => 20,
        ]);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.products.index'))
            ->put(route('admin.products.update', $product), [
                'name' => 'Bakwan',
                'category_id' => $category->id,
                'price' => 3000,
                'stock' => 20,
                'variants' => [
                    ['name' => '1 Porsi (isi 10)', 'price' => 30000, 'stock' => 100],
                ],
            ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'name' => '1 Porsi (isi 10)',
            'stock' => 100,
        ]);
    }
}

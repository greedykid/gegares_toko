<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin order list embeds each order as JSON (selectedOrder) and the detail
 * modal renders its line items from selectedOrder.items. If the list query stops
 * eager loading items, the modal silently shows an empty product table.
 */
class AdminOrderDetailModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_list_embeds_line_items_for_the_detail_modal(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);

        $address = Address::create([
            'user_id' => $customer->id,
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
            'user_id' => $customer->id,
            'order_number' => 'GGR-MODAL-0001',
            'address_id' => $address->id,
            'subtotal' => 16000.00,
            'shipping_cost' => 51000.00,
            'total' => 67000.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'shipping_courier' => 'grab',
            'shipping_service' => 'instant',
        ]);

        $category = Category::create(['name' => 'Gorengan', 'slug' => 'gorengan', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Combro Spesial',
            'slug' => 'combro-spesial',
            'price' => 8000.00,
            'stock' => 10,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => 2,
            'subtotal' => 16000.00,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.index'));

        $response->assertOk();
        // Present only via the embedded order JSON the modal reads from.
        $response->assertSee('Combro Spesial');
    }
}

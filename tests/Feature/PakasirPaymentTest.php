<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class PakasirPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_order_and_generates_pakasir_link(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890'
        ]);

        $category = Category::create([
            'name' => 'Snack',
            'slug' => 'snack',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon',
            'price' => 10000.00,
            'stock' => 50,
            'image' => 'klepon.jpg',
            'is_featured' => true,
        ]);

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

        // Mock Cart
        $cartKey = $product->id . '_0';
        $cartData = [
            $cartKey => [
                'id' => $cartKey,
                'product_id' => $product->id,
                'variant_id' => null,
                'name' => $product->name,
                'variant_name' => null,
                'price' => $product->price,
                'image' => $product->image,
                'slug' => $product->slug,
                'quantity' => 2,
                'stock' => $product->stock,
            ]
        ];
        
        $this->actingAs($user)
            ->withSession(['cart' => $cartData]);

        // Set config variables for Pakasir
        config(['pakasir.project_slug' => 'gegares']);
        config(['pakasir.api_key' => 'test-api-key']);

        $response = $this->post(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
            'shipping_cost' => 9000,
            'payment_method' => 'pakasir',
            'notes' => 'Tolong bungkus rapi',
        ]);

        // Expect redirect to payment page
        $order = Order::first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('orders.payment', $order));

        // Confirm database has Pakasir link and order number matching our slug & amount
        $this->assertNotNull($order->pakasir_link);
        $this->assertNotNull($order->pakasir_order_id);
        $this->assertStringContainsString('https://app.pakasir.com/pay/gegares/', $order->pakasir_link);
        
        $expectedOrderId = 'GGR-' . date('Ymd') . '-' . strtolower(substr($order->order_number, -6));
        $this->assertStringContainsString('order_id=' . $expectedOrderId, $order->pakasir_link);

        // Clear cart session verification
        $this->assertEmpty(Session::get('cart'));
    }

    public function test_webhook_successfully_updates_payment_status_and_decrements_stock(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890'
        ]);

        $category = Category::create([
            'name' => 'Snack',
            'slug' => 'snack',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon',
            'price' => 10000.00,
            'stock' => 50,
            'image' => 'klepon.jpg',
            'is_featured' => true,
        ]);

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
            'order_number' => 'GGR-TEST-0001',
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'pakasir',
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => 2,
            'subtotal' => 20000.00,
        ]);

        // Send post request to webhook/pakasir
        $response = $this->postJson(route('webhook.pakasir'), [
            'amount' => 29000,
            'order_id' => 'GGR-TEST-0001',
            'status' => 'completed',
            'payment_method' => 'qris',
            'completed_at' => '2026-06-15 12:00:00',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        // Verify database updates
        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('qris', $order->payment_method);
        
        $product->refresh();
        // Initial stock was 50, purchased 2, should be 48
        $this->assertEquals(48, $product->stock);
    }
}

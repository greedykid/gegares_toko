<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\BiteshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderCancelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function makeOrder(array $overrides = []): Order
    {
        $user = User::factory()->create(['role' => 'user']);
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
            'latitude' => -6.2243,
            'longitude' => 106.8432,
        ]);

        return Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => 'GGR-CANCEL-'.strtoupper(uniqid()),
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'shipping_courier' => 'grab',
            'shipping_service' => 'same_day',
            'biteship_order_id' => 'biteship-123',
        ], $overrides));
    }

    public function test_admin_can_cancel_a_booked_order(): void
    {
        $biteship = $this->createMock(BiteshipService::class);
        $biteship->expects($this->once())
            ->method('cancelOrder')
            ->willReturn(['success' => true]);
        $this->app->instance(BiteshipService::class, $biteship);

        $order = $this->makeOrder();

        $response = $this->actingAs($this->admin)
            ->from(route('admin.orders.index'))
            ->post(route('admin.orders.cancel-shipping', $order), [
                'cancellation_reason' => 'Stok habis',
            ]);

        $response->assertRedirect(route('admin.orders.index'));
        // Default order is "processing", so cancelling also restores stock.
        $response->assertSessionHas('success');
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_it_keeps_the_order_when_biteship_cancel_fails(): void
    {
        $biteship = $this->createMock(BiteshipService::class);
        $biteship->method('cancelOrder')->willReturn(['error' => 'Pengiriman sudah tidak dapat dibatalkan.']);
        $this->app->instance(BiteshipService::class, $biteship);

        $order = $this->makeOrder();

        $response = $this->actingAs($this->admin)
            ->from(route('admin.orders.index'))
            ->post(route('admin.orders.cancel-shipping', $order));

        $response->assertRedirect(route('admin.orders.index'));
        $response->assertSessionHas('error', 'Pengiriman sudah tidak dapat dibatalkan.');
        $this->assertEquals('processing', $order->fresh()->status);
    }

    public function test_it_cancels_locally_when_no_biteship_booking_exists(): void
    {
        // Booking never succeeded, so Biteship must not be called at all.
        $biteship = $this->createMock(BiteshipService::class);
        $biteship->expects($this->never())->method('cancelOrder');
        $this->app->instance(BiteshipService::class, $biteship);

        $order = $this->makeOrder(['biteship_order_id' => null]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.orders.cancel-shipping', $order));

        $response->assertSessionHas('success');
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_cancelling_a_processing_order_restores_stock(): void
    {
        $biteship = $this->createMock(BiteshipService::class);
        $biteship->method('cancelOrder')->willReturn(['success' => true]);
        $this->app->instance(BiteshipService::class, $biteship);

        $category = Category::create(['name' => 'Snack', 'slug' => 'snack', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon',
            'price' => 10000.00,
            'stock' => 48, // 50 - 2 already sold
        ]);

        $order = $this->makeOrder(['status' => 'processing']);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => 2,
            'subtotal' => 20000.00,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.orders.cancel-shipping', $order))
            ->assertSessionHas('success');

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(50, $product->fresh()->stock);
    }

    public function test_cancelling_a_shipped_order_keeps_stock_deducted(): void
    {
        $biteship = $this->createMock(BiteshipService::class);
        $biteship->method('cancelOrder')->willReturn(['success' => true]);
        $this->app->instance(BiteshipService::class, $biteship);

        $category = Category::create(['name' => 'Snack', 'slug' => 'snack', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon',
            'price' => 10000.00,
            'stock' => 48,
        ]);

        // Already shipped: the goods have left, so stock must stay deducted.
        $order = $this->makeOrder(['status' => 'shipped']);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => 2,
            'subtotal' => 20000.00,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.orders.cancel-shipping', $order))
            ->assertSessionHas('success');

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(48, $product->fresh()->stock);
    }

    public function test_it_rejects_cancelling_a_completed_order(): void
    {
        $biteship = $this->createMock(BiteshipService::class);
        $biteship->expects($this->never())->method('cancelOrder');
        $this->app->instance(BiteshipService::class, $biteship);

        $order = $this->makeOrder(['status' => 'completed']);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.orders.index'))
            ->post(route('admin.orders.cancel-shipping', $order));

        $response->assertRedirect(route('admin.orders.index'));
        $response->assertSessionHas('error');
        $this->assertEquals('completed', $order->fresh()->status);
    }
}

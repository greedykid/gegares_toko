<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\BiteshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Rules that apply once an order is alive: which status may follow which, what a
 * late payment does to a dead order, and how money owed back is tracked.
 */
class OrderLifecycleTest extends TestCase
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
        ]);

        return Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => 'GGR-LIFE-'.strtoupper(uniqid()),
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'shipping_courier' => 'grab',
            'shipping_service' => 'same_day',
        ], $overrides));
    }

    private function bindBiteship(): void
    {
        $biteship = $this->createMock(BiteshipService::class);
        $biteship->method('cancelOrder')->willReturn(['success' => true]);
        $this->app->instance(BiteshipService::class, $biteship);
    }

    // ── A late payment must not revive a dead order ──────────────────────────

    public function test_paying_a_cancelled_order_does_not_revive_it(): void
    {
        $category = Category::create(['name' => 'Snack', 'slug' => 'snack', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon',
            'price' => 10000.00,
            'stock' => 50,
        ]);

        // Auto-cancelled after 24h without payment; the Pakasir link still works.
        $order = $this->makeOrder(['status' => 'cancelled', 'payment_status' => 'expired']);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => 2,
            'subtotal' => 20000.00,
        ]);

        config(['pakasir.api_key' => 'test-key']);
        Http::fake([
            'app.pakasir.com/api/transactiondetail*' => Http::response([
                'transaction' => ['status' => 'completed', 'amount' => 29000, 'payment_method' => 'qris'],
            ], 200),
        ]);

        $this->postJson(route('webhook.pakasir'), [
            'order_id' => $order->order_number,
            'status' => 'completed',
            'amount' => 29000,
        ])->assertOk();

        $order->refresh();

        $this->assertEquals('cancelled', $order->status, 'A cancelled order must stay cancelled.');
        $this->assertEquals(50, $product->fresh()->stock, 'No stock may leave for a cancelled order.');

        // The money did arrive, so it is recorded and flagged for a refund.
        $this->assertEquals('paid', $order->payment_status);
        $this->assertTrue($order->needsRefund());
        $this->assertStringContainsString('PERLU REFUND', $order->admin_note);
    }

    // ── Status transitions ───────────────────────────────────────────────────

    public function test_a_completed_order_cannot_be_moved_back(): void
    {
        $order = $this->makeOrder(['status' => 'completed']);

        $this->actingAs($this->admin)
            ->from(route('admin.orders.index'))
            ->put(route('admin.orders.update', $order), ['status' => 'pending'])
            ->assertSessionHas('error');

        $this->assertEquals('completed', $order->fresh()->status);
    }

    public function test_a_cancelled_order_cannot_be_shipped(): void
    {
        $order = $this->makeOrder(['status' => 'cancelled']);

        $this->actingAs($this->admin)
            ->from(route('admin.orders.index'))
            ->put(route('admin.orders.update', $order), ['status' => 'shipped'])
            ->assertSessionHas('error');

        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_a_normal_forward_transition_still_works(): void
    {
        $order = $this->makeOrder(['status' => 'processing']);

        $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $order), ['status' => 'shipped'])
            ->assertSessionHas('success');

        $this->assertEquals('shipped', $order->fresh()->status);
    }

    public function test_cancelling_from_the_status_dropdown_also_returns_stock(): void
    {
        $this->bindBiteship();

        $category = Category::create(['name' => 'Snack', 'slug' => 'snack', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon',
            'price' => 10000.00,
            'stock' => 48,
        ]);

        $order = $this->makeOrder(['status' => 'processing', 'biteship_order_id' => 'biteship-123']);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => 2,
            'subtotal' => 20000.00,
        ]);

        // The dropdown used to bypass Biteship cancellation and the restock.
        $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $order), ['status' => 'cancelled'])
            ->assertSessionHas('success');

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(50, $product->fresh()->stock, 'The dropdown must restore stock too.');
    }

    // ── Refund tracking ──────────────────────────────────────────────────────

    public function test_cancelling_a_paid_order_leaves_it_owing_a_refund(): void
    {
        $this->bindBiteship();

        $order = $this->makeOrder(['status' => 'processing']);

        $this->actingAs($this->admin)
            ->post(route('admin.orders.cancel-shipping', $order));

        $this->assertTrue($order->fresh()->needsRefund());
    }

    public function test_an_admin_can_record_the_refund(): void
    {
        $order = $this->makeOrder(['status' => 'cancelled', 'payment_status' => 'paid']);
        $this->assertTrue($order->needsRefund());

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.mark-refunded', $order))
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertNotNull($order->refunded_at);
        $this->assertFalse($order->needsRefund());
    }

    public function test_an_order_that_owes_nothing_cannot_be_marked_refunded(): void
    {
        $order = $this->makeOrder(['status' => 'processing', 'payment_status' => 'paid']);

        $this->actingAs($this->admin)
            ->from(route('admin.orders.index'))
            ->patch(route('admin.orders.mark-refunded', $order))
            ->assertSessionHas('error');

        $this->assertNull($order->fresh()->refunded_at);
    }
}

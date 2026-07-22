<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderRefundPendingNotification;
use App\Services\BiteshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BiteshipWebhookTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->address = Address::create([
            'user_id' => $this->user->id,
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
    }

    /**
     * Test status synchronization from Biteship webhook (nested format).
     */
    public function test_webhook_syncs_delivered_status_to_completed_nested(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'GGR-NEST-123',
            'address_id' => $this->address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'shipping_courier' => 'grab',
            'shipping_service' => 'same_day',
            'biteship_order_id' => 'biteship-123',
            'tracking_number' => 'WYB-123',
        ]);

        $response = $this->postJson(route('webhook.biteship'), [
            'event' => 'order.status',
            'data' => [
                'order_id' => 'biteship-123',
                'status' => 'delivered',
                'courier' => [
                    'waybill_id' => 'WYB-123',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals('completed', $order->status);
    }

    /**
     * Test status synchronization from Biteship webhook (flat format).
     */
    public function test_webhook_syncs_shipped_status_flat(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'GGR-FLAT-123',
            'address_id' => $this->address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'shipping_courier' => 'grab',
            'shipping_service' => 'same_day',
            'biteship_order_id' => 'biteship-123',
            'tracking_number' => 'WYB-123',
        ]);

        $response = $this->postJson(route('webhook.biteship'), [
            'event' => 'order.status',
            'order_id' => 'biteship-123',
            'status' => 'in_transit',
            'courier_waybill_id' => 'WYB-123',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals('shipped', $order->status);
    }

    /**
     * Test automated courier re-allocation when courier is rejected.
     */
    public function test_webhook_rejected_triggers_reallocation_under_limit(): void
    {
        // Mock the BiteshipService to simulate re-booking creation
        $mockBiteship = $this->createMock(BiteshipService::class);
        $mockBiteship->expects($this->once())
            ->method('createOrder')
            ->willReturn([
                'success' => true,
                'id' => 'biteship-order-new',
                'courier_tracking_id' => 'ttce-track-new',
                'courier' => [
                    'waybill_id' => 'WYB-track-new',
                ],
            ]);

        $this->app->instance(BiteshipService::class, $mockBiteship);

        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'GGR-REALLOC-123',
            'address_id' => $this->address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'shipping_courier' => 'grab',
            'shipping_service' => 'same_day',
            'biteship_order_id' => 'biteship-old-id',
            'tracking_number' => 'WYB-old',
        ]);

        // Send a rejected status
        $response = $this->postJson(route('webhook.biteship'), [
            'event' => 'order.status',
            'data' => [
                'order_id' => 'biteship-old-id',
                'status' => 'rejected',
                'courier' => [
                    'waybill_id' => 'WYB-old',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order->refresh();

        // The order should have been returned to paid, re-booked, and ended up in processing with new details
        $this->assertEquals('processing', $order->status);
        $this->assertEquals('biteship-order-new', $order->biteship_order_id);
        $this->assertEquals('WYB-track-new', $order->tracking_number);
        $this->assertEquals('ttce-track-new', $order->courier_tracking_id);

        // Cache retry should be 1
        $this->assertEquals(1, Cache::get('biteship_reallocation_retries_'.$order->id));
    }

    /**
     * Test retry limit guard prevents infinite re-allocation loops.
     */
    public function test_webhook_rejected_respects_retry_limit(): void
    {
        // Mock BiteshipService but expect ZERO calls since the retry limit is already exceeded
        $mockBiteship = $this->createMock(BiteshipService::class);
        $mockBiteship->expects($this->never())->method('createOrder');

        $this->app->instance(BiteshipService::class, $mockBiteship);

        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'GGR-LIMIT-123',
            'address_id' => $this->address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'shipping_courier' => 'grab',
            'shipping_service' => 'same_day',
            'biteship_order_id' => 'biteship-old-id',
            'tracking_number' => 'WYB-old',
        ]);

        // Manually set cache reallocation retries to 2 (limit exceeded)
        Cache::put('biteship_reallocation_retries_'.$order->id, 2, 3600);

        // Send a courier_not_found status
        $response = $this->postJson(route('webhook.biteship'), [
            'event' => 'order.status',
            'data' => [
                'order_id' => 'biteship-old-id',
                'status' => 'courier_not_found',
            ],
        ]);

        $response->assertStatus(200);

        $order->refresh();

        // Stays in processing for manual admin action; tracking details are NOT
        // cleared, and no new booking was made.
        $this->assertEquals('processing', $order->status);
        $this->assertEquals('biteship-old-id', $order->biteship_order_id);
    }

    // ── A cancellation from the courier is a real cancellation ───────────────

    /** Build a paid order that is holding two units of a product. */
    private function paidOrderHolding(Product $product, int $qty, array $overrides = []): Order
    {
        $order = Order::create(array_merge([
            'user_id' => $this->user->id,
            'order_number' => 'GGR-BTS-'.strtoupper(uniqid()),
            'address_id' => $this->address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'shipping_courier' => 'grab',
            'shipping_service' => 'same_day',
            'biteship_order_id' => 'biteship-cancel-1',
        ], $overrides));

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => $qty,
            'subtotal' => $product->price * $qty,
        ]);

        return $order;
    }

    private function makeProduct(int $stock): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'snack'],
            ['name' => 'Snack', 'is_active' => true]
        );

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon-'.uniqid(),
            'price' => 10000.00,
            'stock' => $stock,
        ]);
    }

    public function test_a_cancellation_from_biteship_returns_stock_and_flags_the_refund(): void
    {
        Notification::fake();

        // 48 on the shelf because this order is holding 2 of the original 50.
        $product = $this->makeProduct(48);
        $order = $this->paidOrderHolding($product, 2);

        $this->postJson(route('webhook.biteship'), [
            'event' => 'order.status',
            'data' => ['order_id' => 'biteship-cancel-1', 'status' => 'cancelled'],
        ])->assertStatus(200);

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);

        // The webhook used to write the status and nothing else, so the goods
        // stayed reserved forever and the customer was never told about the
        // money they had already paid.
        $this->assertEquals(50, $product->fresh()->stock, 'A cancelled shipment must free its stock.');
        $this->assertTrue($order->needsRefund());
        Notification::assertSentTo($order->user, OrderRefundPendingNotification::class);
    }

    public function test_a_returned_parcel_restocks_even_though_it_had_shipped(): void
    {
        Notification::fake();

        $product = $this->makeProduct(48);
        $order = $this->paidOrderHolding($product, 2, ['status' => 'shipped']);

        $this->postJson(route('webhook.biteship'), [
            'event' => 'order.status',
            'data' => ['order_id' => 'biteship-cancel-1', 'status' => 'returned'],
        ])->assertStatus(200);

        $this->assertEquals('cancelled', $order->fresh()->status);
        // The parcel is physically back with the seller, so the units are real.
        $this->assertEquals(50, $product->fresh()->stock);
    }

    public function test_a_cancellation_while_in_transit_does_not_invent_stock(): void
    {
        Notification::fake();

        $product = $this->makeProduct(48);
        $order = $this->paidOrderHolding($product, 2, ['status' => 'shipped']);

        $this->postJson(route('webhook.biteship'), [
            'event' => 'order.status',
            'data' => ['order_id' => 'biteship-cancel-1', 'status' => 'cancelled'],
        ])->assertStatus(200);

        $this->assertEquals('cancelled', $order->fresh()->status);
        // The goods are with the courier, not on the shelf — counting them back
        // in would sell units the shop does not physically hold.
        $this->assertEquals(48, $product->fresh()->stock);
    }

    // ── A late webhook cannot rewrite history ────────────────────────────────

    public function test_a_late_in_transit_webhook_cannot_reopen_a_completed_order(): void
    {
        $product = $this->makeProduct(48);
        $order = $this->paidOrderHolding($product, 2, ['status' => 'completed']);

        $this->postJson(route('webhook.biteship'), [
            'event' => 'order.status',
            'data' => ['order_id' => 'biteship-cancel-1', 'status' => 'on_the_way'],
        ])->assertStatus(200);

        $this->assertEquals('completed', $order->fresh()->status, 'A delivered order must stay delivered.');
    }

    public function test_a_late_webhook_cannot_resurrect_a_cancelled_order(): void
    {
        $product = $this->makeProduct(50);
        $order = $this->paidOrderHolding($product, 2, ['status' => 'cancelled']);

        $this->postJson(route('webhook.biteship'), [
            'event' => 'order.status',
            'data' => ['order_id' => 'biteship-cancel-1', 'status' => 'delivered'],
        ])->assertStatus(200);

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(50, $product->fresh()->stock, 'A refused transition must not touch stock.');
    }
}

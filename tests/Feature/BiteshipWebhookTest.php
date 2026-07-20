<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Services\BiteshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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
}

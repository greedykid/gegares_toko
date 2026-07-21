<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Services\BiteshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * When automatic booking fails, the order sits in "processing" with no
 * biteship_order_id. The admin modal shows a "Booking Ulang ke Biteship"
 * button for exactly that state; these cover the endpoint behind it.
 */
class AdminRebookBiteshipTest extends TestCase
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
            'order_number' => 'GGR-REBOOK-'.strtoupper(uniqid()),
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

    public function test_admin_can_rebook_an_order_whose_automatic_booking_failed(): void
    {
        $biteship = $this->createMock(BiteshipService::class);
        $biteship->expects($this->once())
            ->method('createOrder')
            ->willReturn([
                'success' => true,
                'id' => 'biteship-order-999',
                'courier' => ['waybill_id' => 'WYB-999'],
            ]);
        $this->app->instance(BiteshipService::class, $biteship);

        // Paid + processing but never booked — the failed-automation state.
        $order = $this->makeOrder(['biteship_order_id' => null]);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.orders.index'))
            ->post(route('admin.orders.process-shipping', $order));

        $response->assertRedirect(route('admin.orders.index'));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('processing', $order->status);
        $this->assertEquals('WYB-999', $order->tracking_number);
    }

    public function test_rebooking_surfaces_the_biteship_error_and_keeps_the_order(): void
    {
        $biteship = $this->createMock(BiteshipService::class);
        $biteship->method('createOrder')->willReturn([
            'error' => 'Koordinat alamat pengiriman belum ditentukan.',
        ]);
        $this->app->instance(BiteshipService::class, $biteship);

        $order = $this->makeOrder(['biteship_order_id' => null]);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.orders.index'))
            ->post(route('admin.orders.process-shipping', $order));

        $response->assertSessionHas('error', 'Koordinat alamat pengiriman belum ditentukan.');
        $this->assertEquals('processing', $order->fresh()->status);
        $this->assertNull($order->fresh()->biteship_order_id);
    }

    public function test_it_refuses_to_rebook_a_completed_order(): void
    {
        $biteship = $this->createMock(BiteshipService::class);
        $biteship->expects($this->never())->method('createOrder');
        $this->app->instance(BiteshipService::class, $biteship);

        $order = $this->makeOrder(['status' => 'completed']);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.orders.index'))
            ->post(route('admin.orders.process-shipping', $order));

        $response->assertSessionHas('error');
        $this->assertEquals('completed', $order->fresh()->status);
    }
}

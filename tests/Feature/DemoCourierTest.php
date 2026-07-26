<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\StoreSetting;
use App\Models\User;
use App\Support\DemoCourier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The stand-in courier exists for demo deployments. The important guarantees are
 * that it stays off unless asked for, that it never invents a phone number
 * belonging to a real stranger, and that it does not contradict the order's own
 * status.
 */
class DemoCourierTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        $user = User::factory()->create(['role' => 'user', 'phone' => '081234567890']);
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
            'order_number' => 'GGR-DEMO-'.strtoupper(uniqid()),
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'shipped',
            'payment_status' => 'paid',
            'paid_at' => now()->subHours(3),
            'shipping_courier' => 'grab',
            'shipping_service' => 'same_day',
        ], $overrides));
    }

    // ── Off unless asked for ─────────────────────────────────────────────────

    public function test_it_is_off_by_default(): void
    {
        $this->assertFalse(DemoCourier::enabled());
        $this->assertNull(DemoCourier::payload($this->makeOrder()));
    }

    public function test_the_customer_endpoint_still_reports_nothing_to_track_by_default(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($order->user)
            ->getJson(route('orders.tracking', $order))
            ->assertOk()
            ->assertJson(['success' => false]);
    }

    public function test_the_admin_endpoint_still_reports_nothing_to_track_by_default(): void
    {
        $order = $this->makeOrder();
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '081200000000']);

        $this->actingAs($admin)
            ->getJson(route('admin.orders.tracking', $order))
            ->assertOk()
            ->assertJson(['success' => false]);
    }

    // ── When enabled ─────────────────────────────────────────────────────────

    public function test_it_fills_the_panel_when_enabled(): void
    {
        config(['app.demo_courier' => true]);
        $order = $this->makeOrder();

        $payload = DemoCourier::payload($order);

        $this->assertTrue($payload['success']);
        $this->assertTrue($payload['demo']);
        $this->assertNotEmpty($payload['courier']['name']);
        $this->assertNotEmpty($payload['courier']['plate_number']);
        $this->assertNotEmpty($payload['history']);
    }

    public function test_the_phone_is_the_shops_own_not_an_invented_number(): void
    {
        config(['app.demo_courier' => true]);
        StoreSetting::create(['contact_whatsapp' => '6281234567890']);

        $payload = DemoCourier::payload($this->makeOrder());

        $this->assertSame('+6281234567890', $payload['courier']['phone']);
    }

    public function test_the_photo_falls_back_to_initials_when_the_file_is_missing(): void
    {
        config(['app.demo_courier' => true]);

        $photo = DemoCourier::payload($this->makeOrder())['courier']['photo'];

        if (file_exists(public_path('images/demo-courier.jpg')) || file_exists(public_path('images/demo-courier.png'))) {
            $this->assertStringContainsString('images/demo-courier', $photo);
        } else {
            // Missing asset must not break the panel — the views draw initials.
            $this->assertNull($photo);
        }
    }

    public function test_the_same_order_always_gets_the_same_driver(): void
    {
        config(['app.demo_courier' => true]);
        $order = $this->makeOrder();

        $first = DemoCourier::payload($order)['courier'];
        $second = DemoCourier::payload($order->fresh())['courier'];

        $this->assertSame($first['name'], $second['name']);
        $this->assertSame($first['plate_number'], $second['plate_number']);
    }

    public function test_every_order_shows_the_same_driver(): void
    {
        config(['app.demo_courier' => true]);

        // The photo is one specific person, so a name or plate that changed
        // between orders would contradict the face.
        $a = DemoCourier::payload($this->makeOrder())['courier'];
        $b = DemoCourier::payload($this->makeOrder())['courier'];

        $this->assertSame('Farhan Adhi', $a['name']);
        $this->assertSame($a['name'], $b['name']);
        $this->assertSame($a['plate_number'], $b['plate_number']);
    }

    // ── It must not contradict the order ─────────────────────────────────────

    public function test_a_pending_order_gets_no_courier_even_when_enabled(): void
    {
        config(['app.demo_courier' => true]);

        $this->assertNull(DemoCourier::payload($this->makeOrder(['status' => 'pending'])));
    }

    public function test_a_cancelled_order_gets_no_courier_even_when_enabled(): void
    {
        config(['app.demo_courier' => true]);

        $this->assertNull(DemoCourier::payload($this->makeOrder(['status' => 'cancelled'])));
    }

    public function test_the_status_tracks_the_order_rather_than_inventing_progress(): void
    {
        config(['app.demo_courier' => true]);

        $this->assertSame('allocated', DemoCourier::payload($this->makeOrder(['status' => 'processing']))['status']);
        $this->assertSame('on_the_way', DemoCourier::payload($this->makeOrder(['status' => 'shipped']))['status']);
        $this->assertSame('delivered', DemoCourier::payload($this->makeOrder(['status' => 'completed']))['status']);
    }

    public function test_the_customer_endpoint_serves_it_when_enabled(): void
    {
        config(['app.demo_courier' => true]);
        $order = $this->makeOrder();

        $this->actingAs($order->user)
            ->getJson(route('orders.tracking', $order))
            ->assertOk()
            ->assertJson(['success' => true, 'demo' => true]);
    }
}

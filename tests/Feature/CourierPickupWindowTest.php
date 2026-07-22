<?php

namespace Tests\Feature;

use App\Jobs\BookBiteshipOrder;
use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\BiteshipService;
use App\Support\CourierSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Same-day couriers collect on demand. An order placed after their hours used
 * to be sent to Biteship as a 'scheduled' delivery, which Biteship refuses —
 * "Courier is not available for scheduled delivery". The three retries were
 * spent within minutes, the admin's re-book button rebuilt the same doomed
 * request, and the order stayed paid, unbooked and unrecoverable.
 *
 * It is now deferred to the next window instead of being attempted and refused.
 */
class CourierPickupWindowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Freeze the clock at a Jakarta wall-clock time. */
    private function jakartaTime(string $time): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-22 '.$time, 'Asia/Jakarta'));
    }

    private function makeOrder(array $overrides = []): Order
    {
        $user = User::factory()->create();
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

        $category = Category::firstOrCreate(['slug' => 'snack'], ['name' => 'Snack', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon-'.uniqid(),
            'price' => 10000.00,
            'stock' => 50,
        ]);

        $order = Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => 'GGR-WIN-'.strtoupper(uniqid()),
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'stock_reserved_at' => now(),
            'shipping_courier' => 'grab',
            'shipping_service' => 'same_day',
        ], $overrides));

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => 2,
            'subtotal' => 20000.00,
        ]);

        return $order;
    }

    // ── The window itself ────────────────────────────────────────────────────

    public function test_it_knows_when_a_same_day_courier_is_collecting(): void
    {
        $this->jakartaTime('10:00');
        $this->assertTrue(CourierSchedule::isOpenNow('grab', 'same_day'));

        $this->jakartaTime('08:00');
        $this->assertFalse(CourierSchedule::isOpenNow('grab', 'same_day'));

        // Grab stops at 14:00, Gojek carries on to 15:00.
        $this->jakartaTime('14:30');
        $this->assertFalse(CourierSchedule::isOpenNow('grab', 'same_day'));
        $this->assertTrue(CourierSchedule::isOpenNow('gojek', 'same_day'));
    }

    public function test_a_courier_without_a_window_is_always_bookable(): void
    {
        $this->jakartaTime('23:00');

        // No window configured, so nothing to wait for.
        $this->assertTrue(CourierSchedule::isOpenNow('paxel', 'same_day'));
        $this->assertNull(CourierSchedule::nextOpening('paxel', 'same_day'));

        // 'instant' is deliberately not treated as a windowed service — its real
        // hours are unknown, and guessing them would be inventing an API rule.
        $this->assertTrue(CourierSchedule::isOpenNow('grab', 'instant'));
    }

    public function test_the_next_opening_is_today_before_nine_and_tomorrow_after_close(): void
    {
        $this->jakartaTime('08:00');
        $this->assertEquals('2026-07-22 09:00', CourierSchedule::nextOpening('grab', 'same_day')->format('Y-m-d H:i'));

        $this->jakartaTime('23:00');
        $this->assertEquals('2026-07-23 09:00', CourierSchedule::nextOpening('grab', 'same_day')->format('Y-m-d H:i'));
    }

    // ── The booking job waits instead of failing ─────────────────────────────

    public function test_the_booking_is_deferred_to_the_next_window(): void
    {
        $this->jakartaTime('23:00');
        Queue::fake();

        // Biteship must not be called at all: there is nothing to ask for yet.
        $biteship = $this->createMock(BiteshipService::class);
        $biteship->expects($this->never())->method('createOrder');

        $order = $this->makeOrder();
        (new BookBiteshipOrder($order->id))->handle($biteship);

        // The re-queue itself is not asserted here: the job defers with
        // release(), which is inert when handle() is invoked directly. What
        // matters is observable — Biteship was not asked, and the order was left
        // intact for the morning rather than failed.

        // And the admin sees a plan rather than an order that looks broken.
        $this->assertStringContainsString('MENUNGGU JAM KURIR', $order->fresh()->admin_note);
        $this->assertStringContainsString('23 Jul 09:00', $order->fresh()->admin_note);
        $this->assertEquals('processing', $order->fresh()->status);
    }

    public function test_deferring_twice_does_not_repeat_the_note(): void
    {
        $this->jakartaTime('23:00');
        Queue::fake();

        $biteship = $this->createMock(BiteshipService::class);
        $order = $this->makeOrder();

        (new BookBiteshipOrder($order->id))->handle($biteship);
        (new BookBiteshipOrder($order->id))->handle($biteship);

        $this->assertEquals(1, substr_count($order->fresh()->admin_note, 'MENUNGGU JAM KURIR'));
    }

    public function test_inside_the_window_the_booking_goes_ahead(): void
    {
        $this->jakartaTime('10:00');
        Queue::fake();

        $biteship = $this->createMock(BiteshipService::class);
        $biteship->expects($this->once())->method('createOrder')->willReturn([
            'success' => true,
            'id' => 'biteship-window-1',
            'courier' => ['waybill_id' => 'WYB-window-1'],
        ]);

        $order = $this->makeOrder();
        (new BookBiteshipOrder($order->id))->handle($biteship);

        $this->assertEquals('biteship-window-1', $order->fresh()->biteship_order_id);
        Queue::assertNotPushed(BookBiteshipOrder::class);
    }

    // ── Never ask Biteship to schedule an on-demand courier ──────────────────

    public function test_create_order_refuses_out_of_hours_instead_of_asking_to_schedule(): void
    {
        $this->jakartaTime('23:00');
        config(['biteship.api_key' => 'test-key']);

        $order = $this->makeOrder();
        $result = app(BiteshipService::class)->createOrder($order);

        // No HTTP call is faked, so reaching Biteship would blow up the test —
        // proof the request is stopped here rather than sent as 'scheduled'.
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('jam operasional', $result['error']);
    }

    // ── The admin is told why, and that it is already handled ────────────────

    public function test_rebooking_out_of_hours_explains_itself(): void
    {
        $this->jakartaTime('23:00');

        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrder();

        $this->actingAs($admin)
            ->from(route('admin.orders.index'))
            ->post(route('admin.orders.process-shipping', $order))
            ->assertSessionHas('error');

        $this->assertStringContainsString(
            'sudah dijadwalkan',
            session('error'),
            'The admin should be told the booking is queued, not just that it failed.'
        );
    }
}

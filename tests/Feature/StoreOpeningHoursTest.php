<?php

namespace Tests\Feature;

use App\Jobs\BookBiteshipOrder;
use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\BiteshipService;
use App\Support\CourierSchedule;
use App\Support\StoreSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * A courier cannot collect from an empty shop.
 *
 * Biteship will happily accept an instant booking at 02:39 — production has the
 * bookings to prove it — but at 02:39 nobody is at Gegares to hand over the
 * food. Pickup therefore waits for the shop as well as the courier, and the
 * customer is told before paying rather than after.
 */
class StoreOpeningHoursTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::create(['store_name' => 'Gegares', 'opens_at' => '06:00', 'closes_at' => '17:00']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function jakartaTime(string $time): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-22 '.$time, 'Asia/Jakarta'));
    }

    // ── The shop's own hours ─────────────────────────────────────────────────

    public function test_it_knows_when_the_shop_is_staffed(): void
    {
        $this->jakartaTime('10:00');
        $this->assertTrue(StoreSchedule::isOpenNow());

        $this->jakartaTime('05:30');
        $this->assertFalse(StoreSchedule::isOpenNow());

        $this->jakartaTime('21:59');
        $this->assertFalse(StoreSchedule::isOpenNow());
    }

    public function test_opening_hours_come_from_the_admin_settings(): void
    {
        StoreSetting::first()->update(['opens_at' => '08:00', 'closes_at' => '20:00']);

        $this->jakartaTime('07:00');
        $this->assertFalse(StoreSchedule::isOpenNow(), 'The shop should follow its configured hours.');

        $this->jakartaTime('19:00');
        $this->assertTrue(StoreSchedule::isOpenNow());
    }

    // ── A closed shop holds every courier, instant included ──────────────────

    public function test_instant_waits_for_the_shop_to_open(): void
    {
        // This is the case the earlier design missed: instant has no courier
        // window, so it used to be booked immediately at any hour — sending a
        // driver to a shuttered shop.
        $this->jakartaTime('02:30');

        $this->assertFalse(CourierSchedule::isOpenNow('gojek', 'instant'));
        $this->assertEquals(
            '2026-07-22 06:00',
            CourierSchedule::nextOpening('gojek', 'instant')->format('Y-m-d H:i')
        );
    }

    public function test_instant_goes_immediately_once_the_shop_is_open(): void
    {
        $this->jakartaTime('07:00');

        $this->assertTrue(CourierSchedule::isOpenNow('gojek', 'instant'));
        $this->assertNull(CourierSchedule::nextOpening('gojek', 'instant'));
    }

    public function test_a_same_day_courier_waits_for_whichever_opens_last(): void
    {
        // Shop opens 06:00 but Grab does not collect until 09:00.
        $this->jakartaTime('07:00');

        $this->assertFalse(CourierSchedule::isOpenNow('grab', 'same_day'));
        $this->assertEquals(
            '2026-07-22 09:00',
            CourierSchedule::nextOpening('grab', 'same_day')->format('Y-m-d H:i')
        );
    }

    public function test_a_courier_that_closed_first_waits_for_tomorrow(): void
    {
        // Shop still open at 14:30, but Grab stopped collecting at 14:00.
        $this->jakartaTime('14:30');

        $this->assertFalse(CourierSchedule::isOpenNow('grab', 'same_day'));
        $this->assertEquals(
            '2026-07-23 09:00',
            CourierSchedule::nextOpening('grab', 'same_day')->format('Y-m-d H:i')
        );
    }

    public function test_the_booking_job_defers_an_after_hours_instant_order(): void
    {
        $this->jakartaTime('21:59');

        $biteship = $this->createMock(BiteshipService::class);
        $biteship->expects($this->never())->method('createOrder');

        $order = $this->makeOrder('gojek', 'instant');
        (new BookBiteshipOrder($order->id))->handle($biteship);

        $this->assertStringContainsString('MENUNGGU JAM KURIR', $order->fresh()->admin_note);
        $this->assertStringContainsString('23 Jul 06:00', $order->fresh()->admin_note);
    }

    // ── What the customer is told ────────────────────────────────────────────

    public function test_the_checkout_page_asks_before_charging_when_shut(): void
    {
        $this->jakartaTime('21:59');

        $this->actingAs($this->customerWithCart())
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Toko Sedang Tutup')
            ->assertSee('Ya, Lanjutkan Bayar')
            ->assertSee('storeOpen: false', false);
    }

    public function test_the_checkout_page_stays_quiet_during_opening_hours(): void
    {
        $this->jakartaTime('10:00');

        $this->actingAs($this->customerWithCart())
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('storeOpen: true', false);
    }

    private function customerWithCart(): User
    {
        $user = User::factory()->create(['phone' => '081234567890']);
        Address::create([
            'user_id' => $user->id,
            'label' => 'Rumah',
            'recipient_name' => 'Test User',
            'phone' => '081234567890',
            'address_line' => 'Jl. Tebet Raya No. 1',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12810',
            'is_primary' => true,
            'area_id' => 'IDNP6IDNC148IDND836',
        ]);

        $product = $this->makeProduct();
        $key = $product->id.'_0';
        session(['cart' => [$key => [
            'id' => $key, 'product_id' => $product->id, 'variant_id' => null,
            'name' => $product->name, 'variant_name' => null,
            'price' => (float) $product->price, 'image' => null,
            'slug' => $product->slug, 'quantity' => 2, 'stock' => $product->stock,
        ]]]);

        return $user;
    }

    private function makeProduct(): Product
    {
        $category = Category::firstOrCreate(['slug' => 'snack'], ['name' => 'Snack', 'is_active' => true]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon-'.uniqid(),
            'price' => 10000.00,
            'stock' => 50,
        ]);
    }

    private function makeOrder(string $courier, string $service): Order
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
        ]);

        return Order::create([
            'user_id' => $user->id,
            'order_number' => 'GGR-STORE-'.strtoupper(uniqid()),
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'stock_reserved_at' => now(),
            'shipping_courier' => $courier,
            'shipping_service' => $service,
        ]);
    }
}

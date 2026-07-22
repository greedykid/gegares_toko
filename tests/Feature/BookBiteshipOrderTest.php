<?php

namespace Tests\Feature;

use App\Jobs\BookBiteshipOrder;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Services\BiteshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookBiteshipOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();

        // These orders ship grab/same_day, which is only collected between
        // 09:00 and 14:00 WIB — outside it the job defers instead of booking.
        // Without pinning the clock these tests would pass or fail depending on
        // what time of day the suite happened to run.
        Carbon::setTestNow(Carbon::parse('2026-07-22 10:00', 'Asia/Jakarta'));

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

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Create an order, defaulting to a paid + processing but unbooked order — the
     * state the job acts on now that payment moves an order straight to processing.
     * Overrides let each test tweak status/payment/booking.
     */
    private function makeOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'user_id' => $this->user->id,
            'order_number' => 'GGR-JOB-'.strtoupper(uniqid()),
            'address_id' => $this->address->id,
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

    /** Drive the real payment path: fake the Pakasir API, then hit the webhook. */
    private function settleViaWebhook(Order $order): void
    {
        config(['pakasir.api_key' => 'test-key']);
        Http::fake([
            'app.pakasir.com/api/transactiondetail*' => Http::response([
                'transaction' => [
                    'status' => 'completed',
                    'amount' => (int) $order->total,
                    'payment_method' => 'qris',
                    'completed_at' => '2026-06-15 12:00:00',
                ],
            ], 200),
        ]);

        $this->postJson(route('webhook.pakasir'), [
            'order_id' => $order->order_number,
            'status' => 'completed',
            'amount' => (int) $order->total,
        ])->assertOk();
    }

    /** A BiteshipService mock whose createOrder returns a successful booking. */
    private function successfulBiteship(): BiteshipService
    {
        $mock = $this->createMock(BiteshipService::class);
        $mock->method('createOrder')->willReturn([
            'success' => true,
            'id' => 'biteship-order-123',
            'courier_tracking_id' => 'ttce-track-123',
            'courier' => ['waybill_id' => 'WYB-track-123'],
        ]);

        return $mock;
    }

    public function test_it_books_a_paid_unbooked_order_and_moves_it_to_processing(): void
    {
        $order = $this->makeOrder();

        (new BookBiteshipOrder($order->id))->handle($this->successfulBiteship());

        $order->refresh();
        $this->assertEquals('processing', $order->status);
        $this->assertEquals('biteship-order-123', $order->biteship_order_id);
        $this->assertEquals('ttce-track-123', $order->courier_tracking_id);
        $this->assertEquals('WYB-track-123', $order->tracking_number);
    }

    public function test_it_skips_an_order_that_is_already_booked(): void
    {
        $order = $this->makeOrder([
            'biteship_order_id' => 'existing-biteship-id',
            'status' => 'processing',
        ]);

        // createOrder must never be called for an already-booked order.
        $biteship = $this->createMock(BiteshipService::class);
        $biteship->expects($this->never())->method('createOrder');

        (new BookBiteshipOrder($order->id))->handle($biteship);

        $order->refresh();
        $this->assertEquals('existing-biteship-id', $order->biteship_order_id);
        $this->assertEquals('processing', $order->status);
    }

    public function test_it_skips_an_order_that_is_not_paid(): void
    {
        // A rolled-back paid transition could leave the job pointing at an order
        // whose status never actually became paid.
        $order = $this->makeOrder(['status' => 'pending', 'payment_status' => 'unpaid']);

        $biteship = $this->createMock(BiteshipService::class);
        $biteship->expects($this->never())->method('createOrder');

        (new BookBiteshipOrder($order->id))->handle($biteship);

        $order->refresh();
        $this->assertEquals('pending', $order->status);
        $this->assertNull($order->biteship_order_id);
    }

    public function test_it_skips_a_missing_order(): void
    {
        $biteship = $this->createMock(BiteshipService::class);
        $biteship->expects($this->never())->method('createOrder');

        // Should simply return without throwing when the order no longer exists.
        (new BookBiteshipOrder(999999))->handle($biteship);
    }

    public function test_it_respects_the_reallocation_retry_limit(): void
    {
        $order = $this->makeOrder();
        Cache::put('biteship_reallocation_retries_'.$order->id, 2, 3600);

        $biteship = $this->createMock(BiteshipService::class);
        $biteship->expects($this->never())->method('createOrder');

        (new BookBiteshipOrder($order->id))->handle($biteship);

        $order->refresh();
        // Left in processing for manual admin action; never re-booked.
        $this->assertEquals('processing', $order->status);
        $this->assertNull($order->biteship_order_id);
    }

    public function test_it_leaves_the_order_processing_when_booking_fails(): void
    {
        $order = $this->makeOrder();

        $biteship = $this->createMock(BiteshipService::class);
        $biteship->method('createOrder')->willReturn([
            'success' => false,
            'error' => 'Koordinat alamat pengiriman belum ditentukan.',
        ]);

        (new BookBiteshipOrder($order->id))->handle($biteship);

        $order->refresh();
        $this->assertEquals('processing', $order->status);
        $this->assertNull($order->biteship_order_id);
    }

    public function test_it_swallows_exceptions_from_the_courier_api(): void
    {
        $order = $this->makeOrder();

        $biteship = $this->createMock(BiteshipService::class);
        $biteship->method('createOrder')->willThrowException(new \RuntimeException('Biteship timeout'));

        // Must not bubble up (a queued job failure would otherwise retry the API).
        (new BookBiteshipOrder($order->id))->handle($biteship);

        $order->refresh();
        $this->assertEquals('processing', $order->status);
        $this->assertNull($order->biteship_order_id);
    }

    public function test_it_is_dispatched_when_payment_settles(): void
    {
        // Fake only the courier booking: the payment confirmation is itself a
        // queued job now (ConfirmPakasirPayment), so it must still run on the
        // sync connection for the webhook to settle the order.
        Queue::fake([BookBiteshipOrder::class]);

        // Bind the service so markOrderPaid's test guard allows dispatch.
        $this->app->instance(BiteshipService::class, $this->createMock(BiteshipService::class));

        $order = $this->makeOrder(['status' => 'pending', 'payment_status' => 'unpaid']);

        $this->settleViaWebhook($order);

        $order->refresh();
        $this->assertEquals('processing', $order->status);
        $this->assertEquals('paid', $order->payment_status);
        Queue::assertPushed(BookBiteshipOrder::class, fn (BookBiteshipOrder $job) => $job->orderId === $order->id);
    }

    public function test_it_is_not_dispatched_when_the_order_is_already_booked(): void
    {
        // See above: only the courier booking is faked so the confirmation job
        // still runs and settles the order.
        Queue::fake([BookBiteshipOrder::class]);

        $this->app->instance(BiteshipService::class, $this->createMock(BiteshipService::class));

        // Already carries a Biteship id, so settling payment must not re-book it.
        $order = $this->makeOrder([
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'biteship_order_id' => 'existing-biteship-id',
        ]);

        $this->settleViaWebhook($order);

        $order->refresh();
        $this->assertEquals('processing', $order->status);
        Queue::assertNotPushed(BookBiteshipOrder::class);
    }
}

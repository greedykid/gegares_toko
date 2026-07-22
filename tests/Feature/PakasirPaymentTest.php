<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\BiteshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Jobs\ConfirmPakasirPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class PakasirPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Courier booking now waits for the shop to be open (06:00–17:00 by
        // default), so a payment settled at midnight defers instead of booking.
        // Pinned inside opening hours to keep these tests about the payment.
        Carbon::setTestNow(Carbon::parse('2026-07-22 10:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_checkout_creates_order_and_generates_pakasir_link(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
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
            'area_id' => 'IDNP6IDNC148IDND836',
            'latitude' => -6.2243,
            'longitude' => 106.8432,
        ]);

        // Shipping is quoted server-side, so a rate has to be available.
        $this->fakeShippingRate('jne', 'reg', 9000);

        // Mock Cart
        $cartKey = $product->id.'_0';
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
            ],
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

        $expectedOrderId = 'GGR-'.date('Ymd').'-'.strtolower(substr($order->order_number, -6));
        $this->assertStringContainsString('order_id='.$expectedOrderId, $order->pakasir_link);

        // Stock is reserved the moment the order is written, so the next
        // customer cannot check out against units this order already holds.
        $this->assertEquals(48, $product->fresh()->stock);

        // Clear cart session verification
        $this->assertEmpty(Session::get('cart'));
    }

    public function test_webhook_settles_payment_without_touching_stock(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
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

        // The webhook no longer trusts the payload: it re-confirms the transaction
        // straight from Pakasir's API. Fake that API call returning a completed txn.
        config(['pakasir.api_key' => 'test-api-key']);
        Http::fake([
            'app.pakasir.com/api/transactiondetail*' => Http::response([
                'transaction' => [
                    'status' => 'completed',
                    'amount' => 29000,
                    'payment_method' => 'qris',
                    'completed_at' => '2026-06-15 12:00:00',
                ],
            ], 200),
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

        // Verify database updates. Payment now moves the order straight to
        // "processing" so the customer never sees an intermediate "paid" state.
        $order->refresh();
        $this->assertEquals('processing', $order->status);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('qris', $order->payment_method);

        $product->refresh();
        // Stock is claimed when the order is written, not when it is paid. This
        // order was built by hand (no reservation), so settling it must leave
        // the shelf exactly as it found it.
        $this->assertEquals(50, $product->stock);
    }

    public function test_webhook_defers_confirmation_to_a_queued_job(): void
    {
        // The API re-confirmation is a slow sweep, so the webhook must hand it to
        // the queue and acknowledge immediately rather than run it inline.
        Queue::fake();

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

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'GGR-QUEUE-0001',
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

        $this->postJson(route('webhook.pakasir'), [
            'amount' => 29000,
            'order_id' => 'GGR-QUEUE-0001',
            'status' => 'completed',
        ])->assertStatus(200)->assertJson(['status' => 'ok']);

        Queue::assertPushed(ConfirmPakasirPayment::class);

        // An already-paid order acknowledges without re-queuing the confirmation.
        $order->update(['payment_status' => 'paid', 'status' => 'processing']);
        Queue::fake();

        $this->postJson(route('webhook.pakasir'), [
            'amount' => 29000,
            'order_id' => 'GGR-QUEUE-0001',
            'status' => 'completed',
        ])->assertStatus(200)->assertJson(['status' => 'ok']);

        Queue::assertNotPushed(ConfirmPakasirPayment::class);

        // An unknown order is still a 404, cheaply and without queuing anything.
        $this->postJson(route('webhook.pakasir'), [
            'order_id' => 'GGR-DOES-NOT-EXIST',
            'status' => 'completed',
        ])->assertStatus(404);

        Queue::assertNotPushed(ConfirmPakasirPayment::class);
    }

    public function test_settling_payment_moves_order_to_processing_and_books_biteship(): void
    {
        // Mock the BiteshipService — the booking job (dispatched by markOrderPaid)
        // runs inline because the test queue is synchronous.
        $mockBiteship = $this->createMock(BiteshipService::class);
        $mockBiteship->expects($this->once())
            ->method('createOrder')
            ->willReturn([
                'success' => true,
                'id' => 'biteship-order-123',
                'courier_tracking_id' => 'ttce-track-123',
                'courier' => [
                    'waybill_id' => 'WYB-track-123',
                ],
            ]);

        $this->app->instance(BiteshipService::class, $mockBiteship);

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

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'GGR-TEST-0002',
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

        // Settle payment through the real webhook path (re-verified via the API).
        config(['pakasir.api_key' => 'test-api-key']);
        Http::fake([
            'app.pakasir.com/api/transactiondetail*' => Http::response([
                'transaction' => [
                    'status' => 'completed',
                    'amount' => 29000,
                    'payment_method' => 'qris',
                    'completed_at' => '2026-06-15 12:00:00',
                ],
            ], 200),
        ]);

        $this->postJson(route('webhook.pakasir'), [
            'order_id' => 'GGR-TEST-0002',
            'status' => 'completed',
            'amount' => 29000,
        ])->assertStatus(200);

        // Order goes straight to processing, and the background job has filled in
        // the Biteship tracking identifiers.
        $order->refresh();
        $this->assertEquals('processing', $order->status);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('biteship-order-123', $order->biteship_order_id);
        $this->assertEquals('ttce-track-123', $order->courier_tracking_id);
        $this->assertEquals('WYB-track-123', $order->tracking_number);
    }
}

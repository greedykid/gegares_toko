<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Services\PakasirService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The payment page polls /pesanan/{order}/status every 2s. That poll used to run
 * the same exhaustive lookup as the webhook — up to 15 sequential Pakasir calls —
 * so a single poll took seconds and, with PHP's per-request session lock, stalled
 * the next one. The page kept saying "menunggu pembayaran" long after the payment
 * had settled. The interactive path must stay a single, quick request.
 */
class PaymentStatusPollingTest extends TestCase
{
    use RefreshDatabase;

    protected function makeOrder(User $user): Order
    {
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
            'order_number' => 'GGR-20260714-abc123',
            'pakasir_order_id' => 'GGR-20260714-abc123',
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
    }

    public function test_a_status_poll_hits_pakasir_at_most_once(): void
    {
        config(['pakasir.api_key' => 'test-api-key', 'pakasir.project_slug' => 'gegares']);

        // Transaction not settled yet — the worst case, where the old code would
        // fall through every slug/casing combination.
        Http::fake([
            'app.pakasir.com/api/transactiondetail*' => Http::response(['transaction' => null], 200),
        ]);

        $user = User::factory()->create(['phone' => '081234567890']);
        $order = $this->makeOrder($user);

        $this->actingAs($user)
            ->getJson(route('orders.status', $order))
            ->assertOk()
            ->assertJson(['payment_status' => 'unpaid']);

        Http::assertSentCount(1);
    }

    public function test_a_poll_returns_paid_without_calling_pakasir_once_the_webhook_has_settled_it(): void
    {
        config(['pakasir.api_key' => 'test-api-key']);
        Http::fake();

        $user = User::factory()->create(['phone' => '081234567890']);
        $order = $this->makeOrder($user);

        // Whatever the webhook already wrote must be reported straight from the DB.
        $order->update(['payment_status' => 'paid', 'status' => 'paid']);

        $this->actingAs($user)
            ->getJson(route('orders.status', $order))
            ->assertOk()
            ->assertJson(['payment_status' => 'paid']);

        Http::assertNothingSent();
    }

    public function test_the_interactive_sync_still_marks_a_settled_order_paid(): void
    {
        config(['pakasir.api_key' => 'test-api-key', 'pakasir.project_slug' => 'gegares']);

        Http::fake([
            'app.pakasir.com/api/transactiondetail*' => Http::response([
                'transaction' => [
                    'status' => 'completed',
                    'amount' => 29000,
                    'payment_method' => 'qris',
                    'completed_at' => '2026-07-14 05:00:00',
                ],
            ], 200),
        ]);

        $user = User::factory()->create(['phone' => '081234567890']);
        $order = $this->makeOrder($user);

        Cache::forget('pakasir_sync_limit_' . $order->id);

        $this->actingAs($user)
            ->getJson(route('orders.status', $order))
            ->assertOk()
            ->assertJson(['payment_status' => 'paid']);

        $this->assertEquals('paid', $order->fresh()->payment_status);
        Http::assertSentCount(1);
    }
}

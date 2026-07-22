<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReconcilePendingPaymentsTest extends TestCase
{
    use RefreshDatabase;

    private function makeUnpaidOrder(): Order
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
            'order_number' => 'GGR-RECON-'.strtoupper(uniqid()),
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'pakasir',
            'pakasir_order_id' => 'GGR-RECON-PAY',
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
        ]);
    }

    /** Pretend the order was created a given time ago, bypassing the timestamp. */
    private function ageOrder(Order $order, \Illuminate\Support\Carbon $createdAt): void
    {
        Order::whereKey($order->id)->update(['created_at' => $createdAt]);
    }

    private function fakePakasirCompleted(int $amount = 29000): void
    {
        config(['pakasir.api_key' => 'test-key']);
        Http::fake([
            'app.pakasir.com/api/transactiondetail*' => Http::response([
                'transaction' => [
                    'status' => 'completed',
                    'amount' => $amount,
                    'payment_method' => 'qris',
                    'completed_at' => '2026-06-15 12:00:00',
                ],
            ], 200),
        ]);
    }

    public function test_it_settles_an_unpaid_order_whose_payment_completed(): void
    {
        $this->fakePakasirCompleted();
        $order = $this->makeUnpaidOrder();
        // Old enough to be past the live payment-page polling window.
        $this->ageOrder($order, now()->subMinutes(15));

        $this->artisan('orders:reconcile-payments')->assertExitCode(0);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
    }

    public function test_it_skips_orders_still_within_the_polling_window(): void
    {
        $this->fakePakasirCompleted();
        $order = $this->makeUnpaidOrder();
        $this->ageOrder($order, now()->subMinutes(2)); // younger than --minutes=10

        $this->artisan('orders:reconcile-payments')->assertExitCode(0);

        $this->assertEquals('unpaid', $order->fresh()->payment_status);
        Http::assertNothingSent();
    }

    public function test_it_skips_orders_past_the_auto_cancel_horizon(): void
    {
        $this->fakePakasirCompleted();
        $order = $this->makeUnpaidOrder();
        $this->ageOrder($order, now()->subHours(25)); // auto-cancel owns these

        $this->artisan('orders:reconcile-payments')->assertExitCode(0);

        $this->assertEquals('unpaid', $order->fresh()->payment_status);
        Http::assertNothingSent();
    }

    public function test_it_does_nothing_without_an_api_key(): void
    {
        config(['pakasir.api_key' => null]);
        $order = $this->makeUnpaidOrder();
        $this->ageOrder($order, now()->subMinutes(15));

        $this->artisan('orders:reconcile-payments')->assertExitCode(0);

        $this->assertEquals('unpaid', $order->fresh()->payment_status);
    }
}

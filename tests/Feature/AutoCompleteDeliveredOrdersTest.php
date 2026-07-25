<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoCompleteDeliveredOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_completes_orders_delivered_more_than_24_hours_ago(): void
    {
        $user = User::factory()->create();
        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Rumah',
            'recipient_name' => 'Test',
            'phone' => '081234567890',
            'address_line' => 'Jl. Test No. 1',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'postal_code' => '12345',
        ]);

        // Delivered 25 hours ago
        $orderOld = Order::create([
            'user_id' => $user->id,
            'order_number' => 'GGR-OLD-001',
            'address_id' => $address->id,
            'subtotal' => 50000,
            'shipping_cost' => 10000,
            'total' => 60000,
            'status' => 'shipped',
            'payment_status' => 'paid',
            'delivered_at' => now()->subHours(25),
        ]);

        // Delivered 5 hours ago (not yet 24 hours)
        $orderRecent = Order::create([
            'user_id' => $user->id,
            'order_number' => 'GGR-RECENT-002',
            'address_id' => $address->id,
            'subtotal' => 50000,
            'shipping_cost' => 10000,
            'total' => 60000,
            'status' => 'shipped',
            'payment_status' => 'paid',
            'delivered_at' => now()->subHours(5),
        ]);

        $this->artisan('orders:auto-complete')
            ->assertSuccessful();

        $this->assertEquals('completed', $orderOld->fresh()->status);
        $this->assertEquals('shipped', $orderRecent->fresh()->status);
    }
}

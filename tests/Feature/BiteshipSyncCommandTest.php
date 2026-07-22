<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderRefundPendingNotification;
use App\Services\BiteshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * The ten-minute safety net that pulls courier status for in-flight orders.
 *
 * It used to carry its own copy of the Biteship status map and write the result
 * straight onto the order, so it obeyed neither the transition rules nor the
 * cancellation path — the two things the webhook had already been taught. These
 * pin the behaviour it shares with every other courier-driven path.
 */
class BiteshipSyncCommandTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stock): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'snack'],
            ['name' => 'Snack', 'is_active' => true]
        );

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon-'.uniqid(),
            'price' => 10000.00,
            'stock' => $stock,
        ]);
    }

    private function makeOrder(Product $product, int $qty, array $overrides = []): Order
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

        $order = Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => 'GGR-SYNC-'.strtoupper(uniqid()),
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            // A live order holds its stock off the shelf.
            'stock_reserved_at' => now(),
            'shipping_courier' => 'grab',
            'shipping_service' => 'same_day',
            'biteship_order_id' => 'biteship-sync-1',
        ], $overrides));

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => $qty,
            'subtotal' => $product->price * $qty,
        ]);

        return $order;
    }

    /** Bind a Biteship that reports the given order status. */
    private function fakeBiteshipReporting(string $status): void
    {
        $biteship = $this->createMock(BiteshipService::class);
        $biteship->method('getOrder')->willReturn([
            'status' => $status,
            'courier' => ['tracking_id' => 'ttce-sync-1', 'waybill_id' => 'WYB-sync-1'],
        ]);
        $this->app->instance(BiteshipService::class, $biteship);
    }

    public function test_it_carries_forward_a_normal_status(): void
    {
        $order = $this->makeOrder($this->makeProduct(48), 2);
        $this->fakeBiteshipReporting('on_the_way');

        $this->artisan('biteship:sync')->assertSuccessful();

        $order->refresh();
        $this->assertEquals('shipped', $order->status);
        $this->assertEquals('ttce-sync-1', $order->courier_tracking_id);
        $this->assertEquals('WYB-sync-1', $order->tracking_number);
    }

    public function test_a_cancellation_returns_stock_and_flags_the_refund(): void
    {
        Notification::fake();

        $product = $this->makeProduct(48);
        $order = $this->makeOrder($product, 2);
        $this->fakeBiteshipReporting('cancelled');

        $this->artisan('biteship:sync')->assertSuccessful();

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);

        // The command used to write the status and nothing else. The stock stayed
        // reserved, and because the order was cancelled by then no later
        // cancellation could ever release it.
        $this->assertEquals(50, $product->fresh()->stock);
        $this->assertNull($order->stock_reserved_at, 'The reservation must be released, not stranded.');
        $this->assertTrue($order->needsRefund());
        Notification::assertSentTo($order->user, OrderRefundPendingNotification::class);
    }

    public function test_a_returned_parcel_restocks_even_though_it_had_shipped(): void
    {
        Notification::fake();

        $product = $this->makeProduct(48);
        $order = $this->makeOrder($product, 2, ['status' => 'shipped']);
        $this->fakeBiteshipReporting('returned');

        $this->artisan('biteship:sync')->assertSuccessful();

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(50, $product->fresh()->stock);
    }

    public function test_it_leaves_orders_that_are_no_longer_in_flight_alone(): void
    {
        // The old filter caught any order whose courier_tracking_id was empty,
        // cancelled ones included, and would happily revive them.
        $product = $this->makeProduct(50);
        $order = $this->makeOrder($product, 2, [
            'status' => 'cancelled',
            'courier_tracking_id' => null,
            'stock_reserved_at' => null,
        ]);
        $this->fakeBiteshipReporting('on_the_way');

        $this->artisan('biteship:sync')->assertSuccessful();

        $this->assertEquals('cancelled', $order->fresh()->status, 'A cancelled order must not be picked up at all.');
        $this->assertEquals(50, $product->fresh()->stock);
    }

    public function test_a_late_report_cannot_walk_a_completed_order_backwards(): void
    {
        $product = $this->makeProduct(48);
        $order = $this->makeOrder($product, 2, ['status' => 'completed']);
        $this->fakeBiteshipReporting('on_the_way');

        // 'completed' is not in flight, so it is not even selected — and the
        // transition guard would refuse it regardless.
        $this->artisan('biteship:sync')->assertSuccessful();

        $this->assertEquals('completed', $order->fresh()->status);
    }
}

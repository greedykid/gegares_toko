<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin product list has to show how many units are on the shelf but
 * already spoken for, because `stock` stopped meaning "in the warehouse" the
 * moment stock started being reserved at checkout.
 */
class AdminReservedStockTest extends TestCase
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

    private function orderHolding(Product $product, int $qty, string $status, ?string $reservedAt = 'now'): Order
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

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'GGR-RSV-'.strtoupper(uniqid()),
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => $status,
            'payment_status' => $status === 'pending' ? 'unpaid' : 'paid',
            'payment_method' => 'pakasir',
            'stock_reserved_at' => $reservedAt ? now() : null,
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => $qty,
            'subtotal' => $product->price * $qty,
        ]);

        return $order;
    }

    private function reservedFor(Product $product): int
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.products.index'))->assertOk();

        return (int) $response->viewData('products')
            ->firstWhere('id', $product->id)
            ->reserved_quantity;
    }

    public function test_a_live_order_counts_as_reserved(): void
    {
        $product = $this->makeProduct(8); // 10 physical, 2 held
        $this->orderHolding($product, 2, 'pending');

        $this->assertEquals(2, $this->reservedFor($product));
    }

    public function test_a_paid_order_being_prepared_still_counts(): void
    {
        $product = $this->makeProduct(8);
        $this->orderHolding($product, 2, 'processing');

        $this->assertEquals(2, $this->reservedFor($product));
    }

    public function test_goods_already_with_the_courier_do_not_count(): void
    {
        // A shipped order keeps its marker — it took stock out and never gives
        // it back — but those units have physically left. Counting them would
        // tell the admin the warehouse holds more than it does.
        $product = $this->makeProduct(8);
        $this->orderHolding($product, 2, 'shipped');

        $this->assertEquals(0, $this->reservedFor($product));
    }

    public function test_a_delivered_order_does_not_count(): void
    {
        $product = $this->makeProduct(8);
        $this->orderHolding($product, 2, 'completed');

        $this->assertEquals(0, $this->reservedFor($product));
    }

    public function test_an_order_predating_reservations_does_not_count(): void
    {
        // No marker: it never took the stock out in the first place.
        $product = $this->makeProduct(10);
        $this->orderHolding($product, 2, 'pending', null);

        $this->assertEquals(0, $this->reservedFor($product));
    }

    public function test_a_cancelled_order_releases_its_hold(): void
    {
        $product = $this->makeProduct(8);
        $order = $this->orderHolding($product, 2, 'processing');

        $this->assertEquals(2, $this->reservedFor($product));

        app(\App\Services\OrderService::class)->cancelAndRelease($order);

        $this->assertEquals(0, $this->reservedFor($product));
        $this->assertEquals(10, $product->fresh()->stock);
    }
}

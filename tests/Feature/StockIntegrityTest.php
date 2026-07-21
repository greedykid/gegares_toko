<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Stock is deducted in exactly one place — when a payment settles. These guard
 * the two ways that invariant used to be broken.
 */
class StockIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stock): Product
    {
        $category = Category::create(['name' => 'Snack', 'slug' => 'snack', 'is_active' => true]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon',
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
            'order_number' => 'GGR-STOCK-'.strtoupper(uniqid()),
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'pakasir',
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
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

    /** Fake Pakasir confirming the payment, then hit the webhook. */
    private function settleViaWebhook(Order $order): void
    {
        config(['pakasir.api_key' => 'test-key']);
        Http::fake([
            'app.pakasir.com/api/transactiondetail*' => Http::response([
                'transaction' => [
                    'status' => 'completed',
                    'amount' => (int) $order->total,
                    'payment_method' => 'qris',
                ],
            ], 200),
        ]);

        $this->postJson(route('webhook.pakasir'), [
            'order_id' => $order->order_number,
            'status' => 'completed',
            'amount' => (int) $order->total,
        ])->assertOk();
    }

    public function test_auto_cancel_does_not_invent_stock_for_unpaid_orders(): void
    {
        // Unpaid orders never deducted stock, so cancelling must not add any back.
        $product = $this->makeProduct(10);
        $order = $this->makeOrder($product, 2);
        $order->forceFill(['created_at' => now()->subHours(30)])->save();

        $this->artisan('orders:auto-cancel --hours=24')->assertSuccessful();

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('expired', $order->payment_status);
        $this->assertEquals(10, $product->fresh()->stock, 'Stock must be untouched.');
    }

    public function test_payment_flags_the_order_when_stock_is_insufficient(): void
    {
        // Only 1 left but 2 were ordered: stock must not go negative, and the
        // order must carry a visible warning for the admin.
        $product = $this->makeProduct(1);
        $order = $this->makeOrder($product, 2);

        $this->settleViaWebhook($order);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals(1, $product->fresh()->stock, 'Stock must not go negative.');
        // The warning lives in admin_note, not in the customer's own note.
        $this->assertStringContainsString('PERLU DICEK', $order->admin_note);
        $this->assertStringContainsString('Klepon x2', $order->admin_note);
        $this->assertNull($order->notes, 'The customer note must stay untouched.');
    }

    public function test_payment_leaves_no_warning_when_stock_is_sufficient(): void
    {
        $product = $this->makeProduct(10);
        $order = $this->makeOrder($product, 2, ['notes' => 'Tolong bungkus rapi']);

        $this->settleViaWebhook($order);

        $order->refresh();
        $this->assertEquals(8, $product->fresh()->stock);
        $this->assertEquals('Tolong bungkus rapi', $order->notes, 'Customer note must be left alone.');
    }
}

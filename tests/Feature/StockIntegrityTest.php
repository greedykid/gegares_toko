<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

/**
 * Stock is reserved when an order is written and handed back when it dies.
 *
 * The old rule — deduct on payment — let any number of customers check out
 * against one remaining unit; all but one of them paid before discovering the
 * shop had nothing to send. These cover both halves of the reservation: it is
 * taken at checkout, and it always comes back.
 */
class StockIntegrityTest extends TestCase
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

    private function makeUser(): User
    {
        return User::factory()->create(['phone' => '081234567890']);
    }

    private function makeAddress(User $user): Address
    {
        return Address::create([
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
    }

    /** @return array<string, mixed> */
    private function cartFor(Product $product, int $qty): array
    {
        $key = $product->id.'_0';

        return [
            $key => [
                'id' => $key,
                'product_id' => $product->id,
                'variant_id' => null,
                'name' => $product->name,
                'variant_name' => null,
                'price' => (float) $product->price,
                'image' => $product->image,
                'slug' => $product->slug,
                'quantity' => $qty,
                'stock' => $product->stock,
            ],
        ];
    }

    private function checkout(User $user, Address $address, Product $product, int $qty, array $session = [])
    {
        $this->fakeShippingRate('jne', 'reg', 9000);

        return $this->actingAs($user)
            ->withSession(array_merge(['cart' => $this->cartFor($product, $qty)], $session))
            ->post(route('checkout.store'), [
                'address_id' => $address->id,
                'shipping_courier' => 'jne',
                'shipping_service' => 'reg',
                'payment_method' => 'pakasir',
            ]);
    }

    // ── The reservation is taken at checkout ─────────────────────────────────

    public function test_checkout_reserves_stock_before_any_payment(): void
    {
        $user = $this->makeUser();
        $address = $this->makeAddress($user);
        $product = $this->makeProduct(10);

        $this->checkout($user, $address, $product, 2);

        $this->assertEquals(1, Order::count());
        $this->assertEquals('unpaid', Order::first()->payment_status);
        $this->assertEquals(8, $product->fresh()->stock, 'Stock must be held from the moment the order exists.');
    }

    public function test_a_second_customer_cannot_check_out_against_reserved_stock(): void
    {
        $product = $this->makeProduct(2);

        $first = $this->makeUser();
        $this->checkout($first, $this->makeAddress($first), $product, 2);

        $this->assertEquals(0, $product->fresh()->stock);
        Session::flush();

        // The last two units are already spoken for. The second customer has to
        // be turned away here — not after paying.
        $second = $this->makeUser();
        $this->checkout($second, $this->makeAddress($second), $product, 2)
            ->assertSessionHas('error');

        $this->assertEquals(1, Order::count(), 'The oversold order must not exist.');
        $this->assertEquals(0, $product->fresh()->stock, 'Stock must never go negative.');
    }

    public function test_a_refused_order_leaves_no_reservation_behind(): void
    {
        $product = $this->makeProduct(1);

        $user = $this->makeUser();
        // Two wanted, only one on the shelf: the whole order rolls back, so the
        // single unit must still be there for the next customer.
        $this->checkout($user, $this->makeAddress($user), $product, 2)
            ->assertSessionHas('error');

        $this->assertEquals(0, Order::count());
        $this->assertEquals(1, $product->fresh()->stock);
    }

    // ── The reservation always comes back ────────────────────────────────────

    public function test_auto_cancel_returns_the_stock_it_was_holding(): void
    {
        $user = $this->makeUser();
        $address = $this->makeAddress($user);
        $product = $this->makeProduct(10);

        $this->checkout($user, $address, $product, 2);
        $this->assertEquals(8, $product->fresh()->stock);

        $order = Order::first();
        $order->forceFill(['created_at' => now()->subHours(30)])->save();

        $this->artisan('orders:auto-cancel --hours=24')->assertSuccessful();

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('expired', $order->payment_status);
        $this->assertEquals(10, $product->fresh()->stock, 'An abandoned order must free its stock.');
    }

    public function test_auto_cancel_also_frees_the_coupon_slot(): void
    {
        $user = $this->makeUser();
        $address = $this->makeAddress($user);
        $product = $this->makeProduct(10);

        // A promo with a single redemption left.
        $coupon = Coupon::create([
            'code' => 'TERBATAS',
            'type' => 'fixed',
            'value' => 5000,
            'min_purchase' => 0,
            'is_active' => true,
            'usage_limit' => 1,
            'used_count' => 0,
        ]);

        $this->checkout($user, $address, $product, 2, [
            'coupon' => ['id' => $coupon->id, 'code' => $coupon->code, 'type' => 'fixed', 'value' => 5000.0],
        ]);

        $this->assertEquals(1, $coupon->fresh()->used_count);

        Order::first()->forceFill(['created_at' => now()->subHours(30)])->save();
        $this->artisan('orders:auto-cancel --hours=24')->assertSuccessful();

        // Without the release, one order nobody ever paid for would burn the
        // whole promo.
        $this->assertEquals(0, $coupon->fresh()->used_count);
        $this->assertTrue($coupon->fresh()->isValid());
    }

    public function test_running_auto_cancel_twice_does_not_double_restock(): void
    {
        $user = $this->makeUser();
        $address = $this->makeAddress($user);
        $product = $this->makeProduct(10);

        $this->checkout($user, $address, $product, 2);
        Order::first()->forceFill(['created_at' => now()->subHours(30)])->save();

        $this->artisan('orders:auto-cancel --hours=24')->assertSuccessful();
        $this->artisan('orders:auto-cancel --hours=24')->assertSuccessful();

        $this->assertEquals(10, $product->fresh()->stock, 'Releasing twice must not invent stock.');
    }
}

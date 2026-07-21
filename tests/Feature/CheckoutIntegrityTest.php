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
 * Everything that decides money or ownership is re-derived server-side when an
 * order is built. These cover the values a client could otherwise dictate.
 */
class CheckoutIntegrityTest extends TestCase
{
    use RefreshDatabase;

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

    private function makeProduct(float $price = 10000): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'snack'],
            ['name' => 'Snack', 'is_active' => true]
        );

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon-'.uniqid(),
            'price' => $price,
            'stock' => 50,
        ]);
    }

    /** Seed the session cart, optionally with a price that no longer matches the product. */
    private function cartFor(Product $product, int $qty = 2, ?float $snapshotPrice = null): array
    {
        $key = $product->id.'_0';

        return [
            $key => [
                'id' => $key,
                'product_id' => $product->id,
                'variant_id' => null,
                'name' => $product->name,
                'variant_name' => null,
                'price' => $snapshotPrice ?? (float) $product->price,
                'image' => $product->image,
                'slug' => $product->slug,
                'quantity' => $qty,
                'stock' => $product->stock,
            ],
        ];
    }

    public function test_a_forged_shipping_cost_is_replaced_by_the_quoted_rate(): void
    {
        $user = $this->makeUser();
        $address = $this->makeAddress($user);
        $product = $this->makeProduct();
        $this->fakeShippingRate('jne', 'reg', 9000);

        // The customer posts a free delivery.
        $this->actingAs($user)
            ->withSession(['cart' => $this->cartFor($product)])
            ->post(route('checkout.store'), [
                'address_id' => $address->id,
                'shipping_courier' => 'jne',
                'shipping_service' => 'reg',
                'shipping_cost' => 0,
                'payment_method' => 'pakasir',
            ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals(9000, (float) $order->shipping_cost, 'Shipping must come from the quote.');
        $this->assertEquals(29000, (float) $order->total);
    }

    public function test_an_unknown_shipping_service_is_refused(): void
    {
        $user = $this->makeUser();
        $address = $this->makeAddress($user);
        $product = $this->makeProduct();
        $this->fakeShippingRate('jne', 'reg', 9000);

        $this->actingAs($user)
            ->withSession(['cart' => $this->cartFor($product)])
            ->post(route('checkout.store'), [
                'address_id' => $address->id,
                'shipping_courier' => 'gojek', // never quoted
                'shipping_service' => 'instant',
                'payment_method' => 'pakasir',
            ])
            ->assertSessionHas('error');

        $this->assertEquals(0, Order::count());
    }

    public function test_an_address_belonging_to_someone_else_is_rejected(): void
    {
        $victim = $this->makeUser();
        $victimAddress = $this->makeAddress($victim);

        $attacker = $this->makeUser();
        $product = $this->makeProduct();
        $this->fakeShippingRate('jne', 'reg', 9000);

        $this->actingAs($attacker)
            ->withSession(['cart' => $this->cartFor($product)])
            ->post(route('checkout.store'), [
                'address_id' => $victimAddress->id,
                'shipping_courier' => 'jne',
                'shipping_service' => 'reg',
                'payment_method' => 'pakasir',
            ])
            ->assertSessionHasErrors('address_id');

        $this->assertEquals(0, Order::count());
    }

    public function test_an_expired_coupon_no_longer_discounts_the_order(): void
    {
        $user = $this->makeUser();
        $address = $this->makeAddress($user);
        $product = $this->makeProduct();
        $this->fakeShippingRate('jne', 'reg', 9000);

        // Applied while valid, expired by the time checkout runs.
        $coupon = Coupon::create([
            'code' => 'HEMAT10',
            'type' => 'percent',
            'value' => 10,
            'min_purchase' => 0,
            'is_active' => true,
            'used_count' => 0,
            'end_date' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->withSession([
                'cart' => $this->cartFor($product),
                'coupon' => ['id' => $coupon->id, 'code' => $coupon->code, 'type' => 'percent', 'value' => 10.0],
            ])
            ->post(route('checkout.store'), [
                'address_id' => $address->id,
                'shipping_courier' => 'jne',
                'shipping_service' => 'reg',
                'payment_method' => 'pakasir',
            ])
            ->assertSessionHas('error');

        $this->assertEquals(0, Order::count());
        $this->assertEquals(0, $coupon->fresh()->used_count, 'A refused coupon must not be counted.');
    }

    public function test_a_coupon_past_its_quota_is_refused(): void
    {
        $user = $this->makeUser();
        $address = $this->makeAddress($user);
        $product = $this->makeProduct();
        $this->fakeShippingRate('jne', 'reg', 9000);

        $coupon = Coupon::create([
            'code' => 'TERBATAS',
            'type' => 'fixed',
            'value' => 5000,
            'min_purchase' => 0,
            'is_active' => true,
            'usage_limit' => 1,
            'used_count' => 1, // already spent
        ]);

        $this->actingAs($user)
            ->withSession([
                'cart' => $this->cartFor($product),
                'coupon' => ['id' => $coupon->id, 'code' => $coupon->code, 'type' => 'fixed', 'value' => 5000.0],
            ])
            ->post(route('checkout.store'), [
                'address_id' => $address->id,
                'shipping_courier' => 'jne',
                'shipping_service' => 'reg',
                'payment_method' => 'pakasir',
            ])
            ->assertSessionHas('error');

        $this->assertEquals(0, Order::count());
        $this->assertEquals(1, $coupon->fresh()->used_count);
    }

    public function test_a_coupon_below_its_minimum_spend_is_refused(): void
    {
        $user = $this->makeUser();
        $address = $this->makeAddress($user);
        $product = $this->makeProduct();
        $this->fakeShippingRate('jne', 'reg', 9000);

        // Applied on a bigger cart, then items were removed.
        $coupon = Coupon::create([
            'code' => 'BIGSPEND',
            'type' => 'fixed',
            'value' => 5000,
            'min_purchase' => 100000,
            'is_active' => true,
            'used_count' => 0,
        ]);

        $this->actingAs($user)
            ->withSession([
                'cart' => $this->cartFor($product, 1), // only 10.000
                'coupon' => ['id' => $coupon->id, 'code' => $coupon->code, 'type' => 'fixed', 'value' => 5000.0],
            ])
            ->post(route('checkout.store'), [
                'address_id' => $address->id,
                'shipping_courier' => 'jne',
                'shipping_service' => 'reg',
                'payment_method' => 'pakasir',
            ])
            ->assertSessionHas('error');

        $this->assertEquals(0, Order::count());
    }

    public function test_a_stale_cart_price_is_replaced_by_the_current_one(): void
    {
        $user = $this->makeUser();
        $address = $this->makeAddress($user);
        $product = $this->makeProduct(10000);
        $this->fakeShippingRate('jne', 'reg', 9000);

        // Session still carries the old 5.000 price.
        $this->actingAs($user)
            ->withSession(['cart' => $this->cartFor($product, 2, 5000)])
            ->post(route('checkout.store'), [
                'address_id' => $address->id,
                'shipping_courier' => 'jne',
                'shipping_service' => 'reg',
                'payment_method' => 'pakasir',
            ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals(10000, (float) $order->items->first()->product_price);
        $this->assertEquals(20000, (float) $order->subtotal);
        $this->assertEquals(29000, (float) $order->total);

        Session::flush();
    }
}

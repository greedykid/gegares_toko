<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\BiteshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * What happens to an order when the shipping quote misbehaves: when the rate
 * moved after the customer saw it, and when Biteship is having a bad day.
 */
class ShippingQuoteIntegrityTest extends TestCase
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

    private function makeProduct(): Product
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
            'stock' => 50,
        ]);
    }

    private function cartFor(Product $product, int $qty = 2): array
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

    /** Post a checkout declaring what the browser had displayed as shipping. */
    private function checkout(User $user, Address $address, Product $product, ?int $shownCost)
    {
        $payload = [
            'address_id' => $address->id,
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
            'payment_method' => 'pakasir',
        ];

        if ($shownCost !== null) {
            $payload['shipping_cost'] = $shownCost;
        }

        return $this->actingAs($user)
            ->withSession(['cart' => $this->cartFor($product)])
            ->post(route('checkout.store'), $payload);
    }

    // ── A rate that moved after the customer saw it ──────────────────────────

    public function test_a_shipping_rate_that_went_up_stops_the_order(): void
    {
        $user = $this->makeUser();
        $address = $this->makeAddress($user);
        $product = $this->makeProduct();

        // Shown 9.000 on the page, but the quote now says 15.000.
        $this->fakeShippingRate('jne', 'reg', 15000);

        $this->checkout($user, $address, $product, 9000)
            ->assertSessionHas('error');

        $this->assertEquals(0, Order::count(), 'Nobody may be billed a price they were never shown.');
    }

    public function test_a_cheaper_rate_goes_through_in_the_customers_favour(): void
    {
        $user = $this->makeUser();
        $address = $this->makeAddress($user);
        $product = $this->makeProduct();

        $this->fakeShippingRate('jne', 'reg', 5000);

        $this->checkout($user, $address, $product, 9000);

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals(5000, (float) $order->shipping_cost);
        $this->assertEquals(25000, (float) $order->total);
    }

    public function test_an_unchanged_rate_is_charged_as_shown(): void
    {
        $user = $this->makeUser();
        $address = $this->makeAddress($user);
        $product = $this->makeProduct();

        $this->fakeShippingRate('jne', 'reg', 9000);

        $this->checkout($user, $address, $product, 9000);

        $this->assertEquals(9000, (float) Order::first()->shipping_cost);
    }

    public function test_a_forged_low_expectation_still_cannot_set_the_price(): void
    {
        $user = $this->makeUser();
        $address = $this->makeAddress($user);
        $product = $this->makeProduct();

        $this->fakeShippingRate('jne', 'reg', 9000);

        // Claiming to have been shown free delivery must not buy free delivery;
        // it is a comparison value, so the order is refused, never discounted.
        $this->checkout($user, $address, $product, 0)
            ->assertSessionHas('error');

        $this->assertEquals(0, Order::count());
    }

    // ── Biteship having a bad day ────────────────────────────────────────────

    public function test_a_failed_rate_lookup_is_not_remembered(): void
    {
        Cache::flush();
        config(['biteship.api_key' => 'test-key']);

        // First call fails, second succeeds — as if Biteship blipped and recovered.
        Http::fakeSequence()
            ->push(['message' => 'server error'], 500)
            ->push(['pricing' => [[
                'courier_code' => 'grab',
                'courier_service_code' => 'instant',
                'type' => 'instant',
                'price' => 12000,
            ]]], 200);

        $biteship = app(BiteshipService::class);
        $items = [['name' => 'Klepon', 'price' => 10000, 'quantity' => 2]];

        $this->assertSame([], $biteship->getShippingRates('IDNP6IDNC148IDND836', $items));

        // The failure used to be cached for five minutes, so this retry would
        // have kept returning nothing long after Biteship was healthy again.
        $recovered = $biteship->getShippingRates('IDNP6IDNC148IDND836', $items);
        $this->assertCount(1, $recovered);
        $this->assertEquals(12000, $recovered[0]['price']);
    }

    public function test_an_empty_but_successful_answer_is_remembered(): void
    {
        Cache::flush();
        config(['biteship.api_key' => 'test-key']);

        // A successful call saying "nothing serves this address" is a real
        // answer, so the second call must not hit the API again.
        Http::fakeSequence()
            ->push(['pricing' => []], 200)
            ->push(['pricing' => [['courier_code' => 'grab', 'type' => 'instant', 'price' => 12000]]], 200);

        $biteship = app(BiteshipService::class);
        $items = [['name' => 'Klepon', 'price' => 10000, 'quantity' => 2]];

        $this->assertSame([], $biteship->getShippingRates('IDNP6IDNC148IDND836', $items));
        $this->assertSame([], $biteship->getShippingRates('IDNP6IDNC148IDND836', $items), 'The cached answer should have been reused.');
    }
}

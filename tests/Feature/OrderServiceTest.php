<?php

namespace Tests\Feature;

use App\Exceptions\PaymentGatewayException;
use App\Models\Address;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PakasirService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function seedCart(Product $product, int $qty = 2): void
    {
        $key = $product->id.'_0';
        Session::put('cart', [
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
        ]);
    }

    protected function makeUserWithAddress(): array
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

        return [$user, $address];
    }

    protected function makeProduct(): Product
    {
        $category = Category::create(['name' => 'Snack', 'slug' => 'snack', 'is_active' => true]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon',
            'price' => 10000.00,
            'stock' => 50,
            'image' => 'klepon.jpg',
        ]);
    }

    public function test_it_creates_an_order_with_items_and_returns_a_payment_url(): void
    {
        [$user, $address] = $this->makeUserWithAddress();
        $product = $this->makeProduct();
        $this->seedCart($product, 2);

        $result = app(OrderService::class)->createFromCart($user, [
            'address_id' => $address->id,
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
            'shipping_cost' => 9000,
            'notes' => 'Tolong bungkus rapi',
        ]);

        $order = $result['order'];
        $this->assertInstanceOf(Order::class, $order);
        $this->assertNotEmpty($result['paymentUrl']);

        // subtotal 2 x 10.000 = 20.000, + ongkir 9.000 = 29.000
        $this->assertEquals(20000, (float) $order->subtotal);
        $this->assertEquals(9000, (float) $order->shipping_cost);
        $this->assertEquals(29000, (float) $order->total);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals('unpaid', $order->payment_status);
        $this->assertEquals('Tolong bungkus rapi', $order->notes);
        $this->assertCount(1, $order->items);
        $this->assertEquals(2, $order->items->first()->quantity);

        // cart is cleared after a successful order
        $this->assertEmpty(Session::get('cart', []));
    }

    public function test_it_applies_the_cart_coupon_and_increments_its_usage(): void
    {
        [$user, $address] = $this->makeUserWithAddress();
        $product = $this->makeProduct();
        $this->seedCart($product, 2); // subtotal 20.000

        $coupon = Coupon::create([
            'code' => 'HEMAT10',
            'type' => 'percent',
            'value' => 10,
            'min_purchase' => 0,
            'is_active' => true,
            'used_count' => 0,
        ]);
        Session::put('coupon', [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => 'percent',
            'value' => 10.0,
        ]);

        $order = app(OrderService::class)->createFromCart($user, [
            'address_id' => $address->id,
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
            'shipping_cost' => 5000,
        ])['order'];

        // 10% of 20.000 = 2.000 discount → total = 20.000 - 2.000 + 5.000 = 23.000
        $this->assertEquals(2000, (float) $order->discount_amount);
        $this->assertEquals(23000, (float) $order->total);
        $this->assertEquals($coupon->id, $order->coupon_id);
        $this->assertEquals(1, $coupon->fresh()->used_count);
    }

    public function test_it_rolls_back_the_order_when_the_gateway_fails(): void
    {
        [$user, $address] = $this->makeUserWithAddress();
        $product = $this->makeProduct();
        $this->seedCart($product, 2);

        // Force the gateway to fail so we can assert the order is rolled back.
        $mock = $this->createMock(PakasirService::class);
        $mock->method('createPaymentUrl')->willReturn(null);
        $this->app->instance(PakasirService::class, $mock);

        try {
            app(OrderService::class)->createFromCart($user, [
                'address_id' => $address->id,
                'shipping_courier' => 'jne',
                'shipping_service' => 'reg',
                'shipping_cost' => 9000,
            ]);
            $this->fail('Expected PaymentGatewayException was not thrown.');
        } catch (PaymentGatewayException $e) {
            // expected
        }

        // No dangling order or items, and the cart is left intact for a retry.
        $this->assertEquals(0, Order::count());
        $this->assertNotEmpty(Session::get('cart', []));
    }
}

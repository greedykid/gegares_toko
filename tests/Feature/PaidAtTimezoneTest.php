<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderPaidNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Pakasir sends `completed_at` without a UTC offset. Parsing it against the app
 * timezone (Asia/Jakarta) treated a UTC value as local time, so the
 * "Pembayaran Berhasil" email reported the payment 7 hours early.
 */
class PaidAtTimezoneTest extends TestCase
{
    use RefreshDatabase;

    protected function makePendingOrder(): Order
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Snack', 'slug' => 'snack', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon',
            'price' => 10000.00,
            'stock' => 50,
            'image' => 'klepon.jpg',
        ]);
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
            'order_number' => 'GGR-TZ-0001',
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

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => 2,
            'subtotal' => 20000.00,
        ]);

        return $order;
    }

    public function test_a_utc_completed_at_from_pakasir_is_stored_as_jakarta_time(): void
    {
        config(['pakasir.api_key' => 'test-api-key', 'pakasir.timezone' => 'UTC']);

        Http::fake([
            'app.pakasir.com/api/transactiondetail*' => Http::response([
                'transaction' => [
                    'status' => 'completed',
                    'amount' => 29000,
                    'payment_method' => 'qris',
                    // 05:00 UTC is 12:00 in Jakarta (+07:00)
                    'completed_at' => '2026-07-14 05:00:00',
                ],
            ], 200),
        ]);

        $order = $this->makePendingOrder();

        $this->postJson(route('webhook.pakasir'), [
            'order_id' => $order->order_number,
            'amount' => 29000,
            'status' => 'completed',
        ])->assertOk();

        $order->refresh();

        $this->assertSame(
            '2026-07-14 12:00',
            $order->paid_at->timezone('Asia/Jakarta')->format('Y-m-d H:i'),
            'A UTC completed_at must be shifted into WIB, not read as if it were already WIB.'
        );
    }

    public function test_the_paid_email_shows_the_payment_time_in_wib(): void
    {
        $order = $this->makePendingOrder();

        // Stored the way PakasirService writes it: parsed in Pakasir's zone, then
        // shifted into the app timezone. 05:00 UTC -> the customer reads 12:00 WIB.
        $order->update([
            'paid_at' => Carbon::parse('2026-07-14 05:00:00', 'UTC')
                ->setTimezone(config('app.timezone')),
        ]);

        $rendered = (new OrderPaidNotification($order->fresh()))
            ->toMail($order->user)
            ->render();

        $this->assertStringContainsString('12:00 WIB', $rendered);
        $this->assertStringNotContainsString('05:00 WIB', $rendered);
    }
}

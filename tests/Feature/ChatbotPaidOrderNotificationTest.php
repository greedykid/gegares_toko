<?php

namespace Tests\Feature;

use App\Livewire\Chatbot;
use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\GeminiService;
use App\Services\PakasirService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChatbotPaidOrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_announces_recently_paid_orders_and_renders_buttons()
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
            'order_number' => 'GGR-TEST-SUCCESS-1',
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            // A settled payment puts the order straight into "processing".
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
            'source' => 'chatbot',
            'paid_at' => now(),
        ]);

        // Start Livewire Chatbot component as authenticated user
        $this->actingAs($user);

        Livewire::test(Chatbot::class)
            ->set('isOpen', true)
            ->call('checkRecentPaidOrders')
            ->assertSet('isOpen', true)
            ->assertSee('Pembayaran untuk pesanan dengan nomor order')
            ->assertSee('#GGR-TEST-SUCCESS-1')
            ->assertSee('Lihat Detail Pesanan')
            ->assertSee('Lihat Riwayat Pesanan');

        // Verify it was marked as acknowledged in session
        $this->assertContains($order->id, session('gegares_acknowledged_paid_orders', []));

        // Create another chatbot component and verify it does not announce it again
        Livewire::test(Chatbot::class)
            ->set('isOpen', true)
            ->call('checkRecentPaidOrders')
            ->assertSet('isOpen', true)
            ->assertSee('Pembayaran untuk pesanan dengan nomor order'); // Will still see it from history, but it shouldn't add a new duplicate message

        // Let's verify history only contains one announcement
        $history = session('gegares_chat_history', []);
        $announcements = array_filter($history, function ($entry) {
            return isset($entry['content']) && str_contains($entry['content'], 'Pembayaran untuk pesanan dengan nomor order');
        });
        $this->assertCount(1, $announcements);
    }

    public function test_it_handles_buy_intent_and_adds_product_to_cart()
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Snack',
            'slug' => 'snack',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon',
            'price' => 10000.00,
            'stock' => 50,
            'image' => 'klepon.jpg',
            'is_featured' => true,
        ]);

        $mockGemini = $this->createMock(GeminiService::class);
        $mockGemini->expects($this->once())
            ->method('chat')
            ->willReturn("Tentu, saya akan memesankan Klepon untuk Kakak.\n---BUY---Klepon|4");

        $this->app->instance(GeminiService::class, $mockGemini);

        $this->actingAs($user);

        Livewire::test(Chatbot::class)
            ->set('isOpen', true)
            ->call('processAi', 'pesankan saya 4 Klepon')
            ->assertSee('Saya sudah berhasil memasukkan')
            ->assertSee('4 porsi Klepon')
            ->assertSee('Bayar Langsung via Chatbot')
            ->assertSee('Buka Halaman Checkout');

        // Verify it was added to the cart
        $cart = session('cart', []);
        $this->assertNotEmpty($cart);
        $cartItem = collect($cart)->first();
        $this->assertEquals($product->id, $cartItem['product_id']);
        $this->assertEquals(4, $cartItem['quantity']);
    }

    public function test_it_handles_checkout_directly_action()
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
            'area_id' => 'IDNP6IDNC148IDND836',
            'latitude' => -6.2243,
            'longitude' => 106.8432,
        ]);

        // Shipping is quoted server-side, so a rate has to be available.
        $this->fakeShippingRate('jne', 'reg', 9000);

        $category = Category::create([
            'name' => 'Snack',
            'slug' => 'snack',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon',
            'price' => 10000.00,
            'stock' => 50,
            'image' => 'klepon.jpg',
            'is_featured' => true,
        ]);

        // Mock PakasirService
        $mockPakasir = $this->createMock(PakasirService::class);
        $mockPakasir->expects($this->once())
            ->method('createPaymentUrl')
            ->willReturn('https://app.pakasir.com/pay/gegares/49000?order_id=GGR-MOCK-CHECKOUT');
        $this->app->instance(PakasirService::class, $mockPakasir);

        // Put product in user's cart session
        $cartKey = $product->id.'_0';
        $cartData = [
            $cartKey => [
                'id' => $cartKey,
                'product_id' => $product->id,
                'variant_id' => null,
                'name' => $product->name,
                'variant_name' => null,
                'price' => $product->price,
                'image' => $product->image,
                'slug' => $product->slug,
                'quantity' => 4,
                'stock' => $product->stock,
            ],
        ];

        $this->actingAs($user)
            ->withSession(['cart' => $cartData]);

        Livewire::test(Chatbot::class)
            ->set('isOpen', true)
            ->call('checkoutDirectly')
            ->assertSee('Silakan pilih kurir pengiriman')
            ->call('placeDirectOrder', 'jne', 'reg', 9000)
            ->assertSee('Hore! Pesanan Kakak dengan nomor order')
            ->assertSee('Bayar Sekarang (Pakasir)')
            ->assertSee('Lihat Detail Pesanan');

        // Verify cart is cleared
        $this->assertEmpty(session('cart', []));

        // Verify order exists in DB with default shipping cost
        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals(40000.00, $order->subtotal);
        $this->assertEquals(9000.00, $order->shipping_cost);
        $this->assertEquals(49000.00, $order->total);
    }

    public function test_it_handles_checkout_directly_action_without_address()
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Snack',
            'slug' => 'snack',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Klepon',
            'slug' => 'klepon',
            'price' => 10000.00,
            'stock' => 50,
            'image' => 'klepon.jpg',
            'is_featured' => true,
        ]);

        // Put product in user's cart session
        $cartKey = $product->id.'_0';
        $cartData = [
            $cartKey => [
                'id' => $cartKey,
                'product_id' => $product->id,
                'variant_id' => null,
                'name' => $product->name,
                'variant_name' => null,
                'price' => $product->price,
                'image' => $product->image,
                'slug' => $product->slug,
                'quantity' => 4,
                'stock' => $product->stock,
            ],
        ];

        $this->actingAs($user)
            ->withSession(['cart' => $cartData]);

        // Trigger checkoutDirectly without adding an address for the user
        Livewire::test(Chatbot::class)
            ->set('isOpen', true)
            ->call('checkoutDirectly')
            ->assertSee('Kakak belum menambahkan alamat pengiriman')
            ->assertSee('Tambah Alamat Sekarang');

        // Cart should NOT be cleared since checkout failed
        $this->assertNotEmpty(session('cart', []));

        // No order should be created
        $this->assertEquals(0, Order::count());
    }

    public function test_it_preserves_assistant_replies_with_suggestions_in_history()
    {
        $user = User::factory()->create();

        // 1. Setup mock chatbot history with suggestions
        $history = [
            [
                'role' => 'user',
                'type' => 'text',
                'content' => 'pesankan saya 4 Klepon',
                'time' => '05:00',
            ],
            [
                'role' => 'assistant',
                'type' => 'text',
                'content' => 'Saya sudah memasukkan Klepon',
                'time' => '05:01',
                'suggestions' => ['Lacak pengiriman'], // suggestions key present!
            ],
        ];

        // 2. Mock Gemini chat method
        $mockGemini = $this->createMock(GeminiService::class);
        $mockGemini->expects($this->once())
            ->method('chat')
            ->with(
                $this->equalTo('saya sudah bayar'),
                $this->anything(),
                $this->callback(function ($historyPassed) {
                    // History should contain the user message and the assistant message (which had suggestions)
                    return count($historyPassed) === 2
                        && $historyPassed[0]['role'] === 'user'
                        && $historyPassed[0]['content'] === 'pesankan saya 4 Klepon'
                        && $historyPassed[1]['role'] === 'assistant'
                        && $historyPassed[1]['content'] === 'Saya sudah memasukkan Klepon';
                })
            )
            ->willReturn('Terima kasih Kak! Pembayaran Anda sudah kami terima.');

        $this->app->instance(GeminiService::class, $mockGemini);

        $hash = hash_hmac('sha256', serialize($history), config('app.key'));

        $this->actingAs($user)
            ->withSession([
                'gegares_chat_history' => $history,
                'gegares_chat_hash' => $hash,
            ]);

        Livewire::test(Chatbot::class)
            ->set('isOpen', true)
            ->call('processAi', 'saya sudah bayar')
            ->assertSee('Terima kasih Kak! Pembayaran Anda sudah kami terima.');
    }

    public function test_it_auto_opens_when_query_parameter_chatbot_open_is_present()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::withQueryParams(['chatbot_open' => '1'])
            ->test(Chatbot::class)
            ->assertSet('isOpen', true);

        $this->assertTrue(session('gegares_chat_open'));
    }

    public function test_it_announces_unpaid_order_when_query_parameter_chatbot_open_is_present()
    {
        $user = User::factory()->create(['phone' => '081234567890']);
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
            'order_number' => 'GGR-TEST-UNPAID-1',
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'pakasir',
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
            'pakasir_link' => 'https://app.pakasir.com/pay/gegares/29000',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('orders.payment', $order).'?chatbot_open=1');

        $response->assertSee('belum selesai atau belum kami terima');
        $response->assertSee('Bayar Sekarang (Pakasir)');
        $response->assertSee('Lihat Detail Pesanan');

        $this->assertTrue(session('gegares_chat_open'));
        $this->assertContains($order->id, session('gegares_acknowledged_unpaid_orders', []));
    }

    public function test_it_auto_opens_and_announces_status_on_payment_page_without_query_param()
    {
        $user = User::factory()->create(['phone' => '081234567890']);
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
            'order_number' => 'GGR-TEST-UNPAID-2',
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'pakasir',
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
            'pakasir_link' => 'https://app.pakasir.com/pay/gegares/29000',
            'source' => 'chatbot',
        ]);

        $this->actingAs($user);

        // Access the payment page without '?chatbot_open=1'
        $response = $this->get(route('orders.payment', $order));

        $response->assertSee('belum selesai atau belum kami terima');
        $this->assertTrue(session('gegares_chat_open'));
    }

    public function test_it_does_not_auto_open_on_payment_page_for_normal_order()
    {
        $user = User::factory()->create(['phone' => '081234567890']);
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
            'order_number' => 'GGR-TEST-NORMAL-1',
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'pakasir',
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
            'pakasir_link' => 'https://app.pakasir.com/pay/gegares/29000',
            'notes' => 'Customer notes here',
        ]);

        $this->actingAs($user);

        // Access the payment page without '?chatbot_open=1'
        $response = $this->get(route('orders.payment', $order));

        $response->assertDontSee('belum selesai atau belum kami terima');
        $this->assertFalse(session('gegares_chat_open', false));
    }

    public function test_it_reads_open_status_from_cookie()
    {
        $user = User::factory()->create(['phone' => '081234567890']);
        $this->actingAs($user);

        // Case 1: Cookie is '1' (open)
        Livewire::withCookies(['gegares_chat_open' => '1'])
            ->test(Chatbot::class)
            ->assertSet('isOpen', true);

        // Case 2: Cookie is '0' (closed)
        Livewire::withCookies(['gegares_chat_open' => '0'])
            ->test(Chatbot::class)
            ->assertSet('isOpen', false);
    }
}

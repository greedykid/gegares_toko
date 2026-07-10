<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The English URLs were renamed to Indonesian ones, but old links survive in
 * places we cannot rewrite: payment return URLs already stored in Pakasir, and
 * password-reset links already sitting in customers' inboxes. They must keep
 * resolving, with their query string intact.
 */
class LegacyUrlRedirectTest extends TestCase
{
    public static function legacyUrlProvider(): array
    {
        return [
            'products index' => ['/products', '/produk'],
            'product detail' => ['/products/klepon', '/produk/klepon'],
            'about' => ['/about', '/tentang'],
            'contact' => ['/contact', '/kontak'],
            'login' => ['/login', '/masuk'],
            'register' => ['/register', '/daftar'],
            'forgot password' => ['/forgot-password', '/lupa-kata-sandi'],
            'reset password' => ['/reset-password/abc123', '/atur-ulang-kata-sandi/abc123'],
            'checkout' => ['/checkout', '/pemesanan'],
            'wishlist' => ['/wishlist', '/favorit'],
            'settings' => ['/settings', '/pengaturan'],
            'orders index' => ['/orders', '/pesanan'],
            'order detail' => ['/orders/7', '/pesanan/7'],
            'order payment' => ['/orders/7/payment', '/pesanan/7/pembayaran'],
            'order tracking' => ['/orders/7/tracking', '/pesanan/7/lacak'],
            'order status' => ['/orders/7/status', '/pesanan/7/status'],
            'admin login' => ['/admin/login', '/admin'],
            'admin dashboard' => ['/admin/dashboard', '/admin/dasbor'],
        ];
    }

    #[DataProvider('legacyUrlProvider')]
    public function test_legacy_url_redirects_permanently(string $old, string $new): void
    {
        $response = $this->get($old);

        $response->assertStatus(301);
        $response->assertRedirect($new);
    }

    public function test_it_carries_the_query_string_across_the_redirect(): void
    {
        // Pakasir returns chatbot orders to this URL; dropping ?chatbot_open=1
        // would silently break the post-payment chatbot handoff.
        $this->get('/orders/1/payment?chatbot_open=1')
            ->assertStatus(301)
            ->assertRedirect('/pesanan/1/pembayaran?chatbot_open=1');
    }
}

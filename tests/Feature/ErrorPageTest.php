<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The error pages are the one part of the site that has to work when the rest of
 * it does not, so they get checked the same way a customer meets them: through a
 * real request, with debug off.
 */
class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // With debug on, Laravel shows its own trace page instead of these views.
        config(['app.debug' => false]);
    }

    public function test_an_unknown_url_renders_the_branded_404(): void
    {
        $response = $this->get('/halaman-yang-tidak-pernah-ada');

        $response->assertNotFound();
        $response->assertSee('Halaman ini tidak ada');
        $response->assertSee('Kembali ke Beranda');
        $response->assertDontSee('Not Found', false);   // Laravel's bare default
    }

    public function test_opening_someone_elses_order_renders_the_branded_403(): void
    {
        $owner = User::factory()->create(['role' => 'user', 'phone' => '081234567890']);
        $address = Address::create([
            'user_id' => $owner->id,
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
            'user_id' => $owner->id,
            'address_id' => $address->id,
            'order_number' => 'GGR-ERR-0001',
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);

        $intruder = User::factory()->create(['role' => 'user', 'phone' => '081200000000']);

        $response = $this->actingAs($intruder)->get(route('orders.show', $order->id));

        $response->assertForbidden();
        $response->assertSee('tidak punya akses');
    }

    public function test_every_error_view_renders(): void
    {
        foreach ([400, 401, 403, 404, 419, 429] as $code) {
            $html = view("errors.{$code}", ['exception' => null])->render();

            $this->assertStringContainsString((string) $code, $html, "errors.{$code} kehilangan kodenya");
            $this->assertStringContainsString('Gegares', $html, "errors.{$code} tidak memakai layout");
        }
    }

    /**
     * These two must not depend on the app being healthy: no layout, no
     * Livewire, no database read, no compiled asset. 500 is served when
     * something is already broken, and 503 during a deploy — with migrations
     * running and assets being swapped. If either is later changed to extend
     * layouts.app, a database outage would take the error page down with it and
     * the customer would land on Laravel's bare page instead.
     */
    public function test_the_500_and_503_pages_stand_alone(): void
    {
        foreach ([500 => 'Ada gangguan di sisi kami', 503 => 'Sedang dalam pemeliharaan'] as $code => $heading) {
            $source = file_get_contents(resource_path("views/errors/{$code}.blade.php"));

            $this->assertStringNotContainsString('@extends', $source, "errors.{$code} tidak boleh memakai layout");
            $this->assertStringNotContainsString('@livewire', $source);
            $this->assertStringNotContainsString('@vite', $source);

            $html = view("errors.{$code}", ['exception' => null])->render();

            $this->assertStringContainsString('<!DOCTYPE html>', $html);
            $this->assertStringContainsString($heading, $html);
            $this->assertStringContainsString('<style>', $html);
        }
    }

    public function test_no_error_page_leaks_internal_detail(): void
    {
        foreach ([400, 401, 403, 404, 419, 429, 500, 503] as $code) {
            $html = view("errors.{$code}", ['exception' => null])->render();

            foreach (['Stack trace', 'vendor/laravel', 'APP_KEY', 'DB_PASSWORD'] as $leak) {
                $this->assertStringNotContainsString($leak, $html, "errors.{$code} membocorkan {$leak}");
            }
        }
    }

    /**
     * A session that outlives SESSION_LIFETIME leaves a stale CSRF token on an
     * open tab, and the next submit is answered 419. Livewire shows its own
     * dialog for that; a plain form POST lands on this page.
     *
     * Driven through abort(419) rather than a forged token, because the
     * framework skips CSRF verification whenever APP_ENV is "testing" — a real
     * stale token can never produce a 419 in the suite. What is worth locking
     * down is the same thing either way: that a 419 resolves to our view and
     * not Laravel's bare "Page Expired".
     */
    public function test_a_419_renders_the_branded_session_expired_page(): void
    {
        \Illuminate\Support\Facades\Route::middleware('web')
            ->get('/__test_419', fn () => abort(419));

        $response = $this->get('/__test_419');

        $response->assertStatus(419);
        $response->assertSee('Sesi kamu sudah berakhir');
        $response->assertSee('Muat Ulang Halaman');
        $response->assertDontSee('Page Expired', false);
    }

    public function test_a_throttled_request_is_told_how_long_to_wait(): void
    {
        $exception = new \Illuminate\Http\Exceptions\ThrottleRequestsException(
            'Too Many Attempts.', null, ['Retry-After' => 90]
        );

        $html = view('errors.429', ['exception' => $exception])->render();

        $this->assertStringContainsString('2 menit', $html);
    }

    public function test_the_429_page_copes_with_no_retry_after_header(): void
    {
        $html = view('errors.429', ['exception' => null])->render();

        $this->assertStringContainsString('Terlalu banyak percobaan', $html);
        $this->assertStringContainsString('Tunggu sebentar', $html);
    }
}

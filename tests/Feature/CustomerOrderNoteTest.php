<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Support\CustomerOrderNote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `orders.admin_note` is the staff trail — courier status codes, internal
 * to-dos, and instructions aimed at the shop. The customer gets the same events
 * restated, and must never get the raw text.
 */
class CustomerOrderNoteTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        // Phone set: check_phone middleware would otherwise bounce the customer
        // to the complete-profile page instead of their order.
        $user = User::factory()->create(['role' => 'user', 'phone' => '081234567890']);
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

        return Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => 'GGR-NOTE-'.strtoupper(uniqid()),
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'shipping_courier' => 'grab',
            'shipping_service' => 'same_day',
        ], $overrides));
    }

    // ── The shop's own problems stay the shop's ──────────────────────────────

    public function test_an_impossible_pickup_does_not_leak_the_misconfiguration(): void
    {
        $order = $this->makeOrder([
            'admin_note' => 'TIDAK BISA DIJEMPUT: jam buka toko dan jam jemput GRAB tidak beririsan. Perbaiki jam buka toko atau booking manual.',
        ]);

        $notes = CustomerOrderNote::for($order);

        $this->assertCount(1, $notes);
        $this->assertStringContainsString('secara manual', $notes[0]);

        // None of the staff-only wording may survive the translation.
        foreach (['TIDAK BISA DIJEMPUT', 'Perbaiki jam buka toko', 'booking manual', 'jam jemput'] as $internal) {
            $this->assertStringNotContainsString($internal, $notes[0]);
        }
    }

    public function test_a_courier_cancellation_drops_the_raw_status_code(): void
    {
        $order = $this->makeOrder([
            'status' => 'cancelled',
            'payment_status' => 'paid',
            'admin_note' => 'Dibatalkan otomatis dari Biteship (status: rejected).',
        ]);

        $notes = CustomerOrderNote::for($order);

        $this->assertStringNotContainsString('rejected', $notes[0]);
        $this->assertStringNotContainsString('Biteship', $notes[0]);
        // Paid + cancelled means money is owed back; say so.
        $this->assertStringContainsString('dikembalikan', $notes[0]);
    }

    public function test_an_unpaid_courier_cancellation_does_not_promise_a_refund(): void
    {
        $order = $this->makeOrder([
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
            'admin_note' => 'Dibatalkan otomatis dari Biteship (status: rejected).',
        ]);

        $this->assertStringNotContainsString('dikembalikan', CustomerOrderNote::for($order)[0]);
    }

    // ── Useful detail is carried over ────────────────────────────────────────

    public function test_a_deferred_pickup_keeps_the_scheduled_time(): void
    {
        $order = $this->makeOrder([
            'admin_note' => 'MENUNGGU JAM KURIR: di luar jam operasional GRAB, booking otomatis dijadwalkan 12 Aug 08:00 WIB.',
        ]);

        $notes = CustomerOrderNote::for($order);

        $this->assertStringContainsString('12 Aug 08:00 WIB', $notes[0]);
        $this->assertStringNotContainsString('MENUNGGU JAM KURIR', $notes[0]);
    }

    public function test_an_auto_cancellation_keeps_the_deadline(): void
    {
        $order = $this->makeOrder([
            'status' => 'cancelled',
            'payment_status' => 'expired',
            'admin_note' => 'Otomatis dibatalkan setelah 24 jam tanpa pembayaran.',
        ]);

        $this->assertStringContainsString('24 jam', CustomerOrderNote::for($order)[0]);
    }

    public function test_a_refund_owed_is_announced(): void
    {
        $order = $this->makeOrder([
            'status' => 'cancelled',
            'admin_note' => 'PERLU REFUND: pembayaran diterima setelah pesanan dibatalkan.',
        ]);

        $notes = CustomerOrderNote::for($order);

        $this->assertStringNotContainsString('PERLU REFUND', $notes[0]);
        $this->assertStringContainsString('dikembalikan', $notes[0]);
    }

    // ── Composition and the silent-failure rule ──────────────────────────────

    public function test_a_multi_event_trail_becomes_one_line_per_event(): void
    {
        $order = $this->makeOrder([
            'admin_note' => 'MENUNGGU JAM KURIR: di luar jam operasional GRAB, booking otomatis dijadwalkan 12 Aug 08:00 WIB.'
                .' | TIDAK BISA DIJEMPUT: jam buka toko dan jam jemput GRAB tidak beririsan. Perbaiki jam buka toko atau booking manual.',
        ]);

        $this->assertCount(2, CustomerOrderNote::for($order));
    }

    public function test_an_unrecognised_entry_is_dropped_rather_than_guessed_at(): void
    {
        $order = $this->makeOrder([
            'admin_note' => 'Dicatat manual oleh admin: pelanggan minta dikirim lewat teras belakang.',
        ]);

        $this->assertSame([], CustomerOrderNote::for($order));
    }

    public function test_an_empty_trail_produces_nothing(): void
    {
        $this->assertSame([], CustomerOrderNote::for($this->makeOrder(['admin_note' => null])));
        $this->assertSame([], CustomerOrderNote::for($this->makeOrder(['admin_note' => '   '])));
    }

    // ── End to end on the customer's page ────────────────────────────────────

    public function test_the_order_page_shows_the_translation_and_not_the_raw_trail(): void
    {
        $order = $this->makeOrder([
            'admin_note' => 'TIDAK BISA DIJEMPUT: jam buka toko dan jam jemput GRAB tidak beririsan. Perbaiki jam buka toko atau booking manual.',
        ]);

        $response = $this->actingAs($order->user)->get(route('orders.show', $order->id));

        $response->assertOk();
        $response->assertSee('Informasi Pesanan');
        $response->assertSee('secara manual', false);
        $response->assertDontSee('Perbaiki jam buka toko');
        $response->assertDontSee('TIDAK BISA DIJEMPUT');
    }

    public function test_the_section_is_absent_when_there_is_nothing_to_say(): void
    {
        $order = $this->makeOrder(['admin_note' => null]);

        $this->actingAs($order->user)
            ->get(route('orders.show', $order->id))
            ->assertOk()
            ->assertDontSee('Informasi Pesanan');
    }
}

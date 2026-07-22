<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The order list pages 10 at a time and appends the next batch in place, so the
 * partial endpoint has to return renderable cards and the link to the batch
 * after it — while keeping whatever status filter is active.
 */
class OrderListLoadMoreTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['phone' => '081234567890']);
    }

    private function makeOrders(int $count, string $status = 'completed'): void
    {
        $address = Address::create([
            'user_id' => $this->user->id,
            'label' => 'Rumah',
            'recipient_name' => 'Test User',
            'phone' => '081234567890',
            'address_line' => 'Jl. Tebet Raya No. 1',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12810',
            'is_primary' => true,
        ]);

        foreach (range(1, $count) as $i) {
            Order::create([
                'user_id' => $this->user->id,
                'order_number' => sprintf('GGR-LIST-%s-%03d', strtoupper($status), $i),
                'address_id' => $address->id,
                'subtotal' => 20000,
                'shipping_cost' => 9000,
                'total' => 29000,
                'status' => $status,
                'payment_status' => 'paid',
                'payment_method' => 'qris',
                'shipping_courier' => 'jne',
                'shipping_service' => 'reg',
            ]);
        }
    }

    public function test_the_first_page_offers_a_next_batch_and_no_page_links(): void
    {
        $this->makeOrders(12);

        $response = $this->actingAs($this->user)->get(route('orders.index'));

        $response->assertOk();
        $response->assertSee('Muat Lebih Banyak');
        // Page links only survive inside <noscript>, so the visible pager is gone.
        $response->assertSee('<noscript>', false);
    }

    public function test_the_partial_returns_the_next_batch_of_cards(): void
    {
        $this->makeOrders(12);

        $response = $this->actingAs($this->user)
            ->getJson(route('orders.index', ['partial' => 1, 'page' => 2]));

        $response->assertOk();
        $response->assertJsonStructure(['html', 'next_page_url']);

        // 12 orders, 10 per page → page 2 holds the remaining 2 and ends the list.
        $this->assertNull($response->json('next_page_url'));
        $this->assertStringContainsString('GGR-LIST-COMPLETED-', $response->json('html'));
    }

    public function test_the_last_page_reports_no_further_batch(): void
    {
        $this->makeOrders(4);

        $response = $this->actingAs($this->user)
            ->getJson(route('orders.index', ['partial' => 1]));

        $response->assertOk();
        $this->assertNull($response->json('next_page_url'), 'A single page must not offer a next batch.');
    }

    public function test_the_status_filter_survives_into_the_next_batch(): void
    {
        $this->makeOrders(12, 'completed');
        $this->makeOrders(3, 'cancelled');

        $response = $this->actingAs($this->user)
            ->getJson(route('orders.index', ['partial' => 1, 'status' => 'cancelled']));

        $response->assertOk();

        $html = $response->json('html');
        $this->assertStringContainsString('GGR-LIST-CANCELLED-', $html);
        $this->assertStringNotContainsString('GGR-LIST-COMPLETED-', $html, 'The filter must not leak other statuses.');
    }
}

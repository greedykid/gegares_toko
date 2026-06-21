<?php

namespace Tests\Feature;

use App\Models\Order;
use Tests\TestCase;

class OrderTrackingUrlTest extends TestCase
{
    public function test_tracking_url_prioritizes_biteship_order_id(): void
    {
        $order = new Order([
            'biteship_order_id' => '69d581a4b95f2a1100000001',
            'courier_tracking_id' => 'ttce-track-123',
            'tracking_number' => 'WYB-track-123',
        ]);

        // When Biteship API Key is live
        config(['biteship.api_key' => 'biteship_live_123456789']);

        $this->assertEquals(
            'https://track.biteship.com/69d581a4b95f2a1100000001',
            $order->tracking_url
        );
    }

    public function test_tracking_url_appends_sandbox_flag_when_api_key_is_sandbox_and_id_is_biteship(): void
    {
        $order = new Order([
            'biteship_order_id' => '69d581a4b95f2a1100000001',
        ]);

        // When Biteship API Key is sandbox
        config(['biteship.api_key' => 'biteship_test.123456789']);

        $this->assertEquals(
            'https://track.biteship.com/69d581a4b95f2a1100000001?environment=development',
            $order->tracking_url
        );
    }

    public function test_tracking_url_falls_back_to_courier_tracking_id(): void
    {
        $order = new Order([
            'courier_tracking_id' => 'ttce-track-123',
            'tracking_number' => 'WYB-track-123',
        ]);

        config(['biteship.api_key' => 'biteship_live_123456789']);

        $this->assertEquals(
            'https://track.biteship.com/ttce-track-123',
            $order->tracking_url
        );
    }

    public function test_tracking_url_falls_back_to_tracking_number(): void
    {
        $order = new Order([
            'tracking_number' => 'WYB-track-123',
        ]);

        config(['biteship.api_key' => 'biteship_live_123456789']);

        $this->assertEquals(
            'https://track.biteship.com/WYB-track-123',
            $order->tracking_url
        );
    }

    public function test_tracking_url_appends_sandbox_flag_for_legacy_biteship_ids(): void
    {
        config(['biteship.api_key' => 'biteship_test.123456789']);

        // Case 1: ttce prefix
        $order1 = new Order(['courier_tracking_id' => 'ttce-123']);
        $this->assertEquals(
            'https://track.biteship.com/ttce-123?environment=development',
            $order1->tracking_url
        );

        // Case 2: WYB prefix
        $order2 = new Order(['tracking_number' => 'WYB-123']);
        $this->assertEquals(
            'https://track.biteship.com/WYB-123?environment=development',
            $order2->tracking_url
        );
    }

    public function test_tracking_url_returns_empty_when_no_ids_present(): void
    {
        $order = new Order([]);
        $this->assertEquals('', $order->tracking_url);
    }
}

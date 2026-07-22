<?php

namespace Tests;

use App\Services\BiteshipService;
use App\Support\StoreSchedule;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // StoreSchedule memoises trading hours in a static, which survives
        // between tests in the same process. Left alone, one test's opening
        // hours decide another's pickups.
        StoreSchedule::forgetCachedHours();
    }

    /**
     * Bind a BiteshipService that quotes one courier service.
     *
     * OrderService re-quotes shipping from Biteship instead of trusting the cost
     * the browser posts, so any test that places an order has to make a rate
     * available — otherwise checkout correctly refuses to price the delivery.
     */
    protected function fakeShippingRate(string $courier = 'jne', string $service = 'reg', int $price = 9000): void
    {
        $biteship = $this->createMock(BiteshipService::class);

        $biteship->method('getShippingRates')->willReturn([
            [
                'courier_code' => $courier,
                'courier_service_code' => $service,
                'courier_service_name' => 'Reguler',
                'price' => $price,
                'duration' => '1-2 hari',
            ],
        ]);

        $this->app->instance(BiteshipService::class, $biteship);
    }
}

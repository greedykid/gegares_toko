<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Services\BiteshipService;
use App\Livewire\ManageAddresses;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageAddressesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_soft_deletes_address_with_active_orders_successfully(): void
    {
        // Mock the BiteshipService deleteLocation call
        $mockBiteship = $this->createMock(BiteshipService::class);
        $mockBiteship->expects($this->once())
            ->method('deleteLocation')
            ->with('loc-12345')
            ->willReturn(true);
        
        $this->app->instance(BiteshipService::class, $mockBiteship);

        $user = User::factory()->create();
        
        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Rumah Utama',
            'recipient_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address_line' => 'Jl. Jembatan Besi No. 5',
            'city' => 'Jakarta Barat',
            'province' => 'DKI Jakarta',
            'postal_code' => '11320',
            'is_primary' => true,
            'biteship_location_id' => 'loc-12345',
        ]);

        // Create an order referencing this address
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'GGR-20260621-ABC123',
            'address_id' => $address->id,
            'subtotal' => 20000.00,
            'shipping_cost' => 9000.00,
            'total' => 29000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($user);

        // Test Livewire ManageAddresses component
        Livewire::test(ManageAddresses::class, ['selectedAddressId' => $address->id])
            ->assertSet('selectedAddressId', $address->id)
            ->call('deleteAddress', $address->id)
            ->assertSet('selectedAddressId', null);

        // Assert the address is soft-deleted
        $address->refresh();
        $this->assertNotNull($address->deleted_at);

        // Assert the address is not returned in active list
        $this->assertFalse(Address::where('id', $address->id)->exists());

        // Assert the order can still resolve the address details withTrashed
        $order->refresh();
        $this->assertNotNull($order->address);
        $this->assertEquals('Rumah Utama', $order->address->label);
        $this->assertEquals('Budi Santoso', $order->address->recipient_name);
    }

    public function test_it_shows_delete_confirmation_modal_and_deletes_successfully(): void
    {
        // Mock the BiteshipService deleteLocation call
        $mockBiteship = $this->createMock(BiteshipService::class);
        $mockBiteship->expects($this->once())
            ->method('deleteLocation')
            ->with('loc-12345')
            ->willReturn(true);
        
        $this->app->instance(BiteshipService::class, $mockBiteship);

        $user = User::factory()->create();
        
        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Rumah Kedua',
            'recipient_name' => 'Siti Aminah',
            'phone' => '081234567891',
            'address_line' => 'Jl. Tebet Barat No. 10',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12810',
            'is_primary' => true,
            'biteship_location_id' => 'loc-12345',
        ]);

        $this->actingAs($user);

        // Test Livewire ManageAddresses component modal flow
        Livewire::test(ManageAddresses::class, ['selectedAddressId' => $address->id])
            ->assertSet('selectedAddressId', $address->id)
            ->assertSet('showDeleteModal', false)
            ->call('confirmDelete', $address->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('addressIdToDelete', $address->id)
            ->call('deleteAddress')
            ->assertSet('showDeleteModal', false)
            ->assertSet('addressIdToDelete', null)
            ->assertSet('selectedAddressId', null);

        // Assert the address is soft-deleted
        $address->refresh();
        $this->assertNotNull($address->deleted_at);
    }
}

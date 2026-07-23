<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Address;
use App\Services\BiteshipService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class SelectShipping extends Component
{
    public array $cartItems = [];
    public array $rates = [];
    public ?string $selectedRate = null;
    public ?string $selectedAddressId = null;
    public bool $hasValidArea = true;

    public function mount(array $cartItems = [], ?string $selectedAddressId = null)
    {
        $this->cartItems = $cartItems;

        // Auto-detect selected address on initial page load
        if ($selectedAddressId) {
            $this->selectedAddressId = $selectedAddressId;
        } else {
            // Fallback: pick the user's primary (or first) address
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $primaryAddress = $user?->addresses()
                ->orderByDesc('is_primary')
                ->first();
            if ($primaryAddress) {
                $this->selectedAddressId = (string) $primaryAddress->id;
            }
        }

        if ($this->selectedAddressId) {
            $this->fetchRates();
        }
    }

    #[On('addressSelected')]
    public function handleAddressSelected($addressId)
    {
        $this->selectedAddressId = $addressId;
        $this->fetchRates();
    }

    public function fetchRates()
    {
        $this->rates = [];
        $this->selectedRate = null;
        $this->hasValidArea = true;

        if (!$this->selectedAddressId) {
            return;
        }

        $address = Address::find($this->selectedAddressId);
        
        if (!$address || empty($address->area_id)) {
            $this->hasValidArea = false;
            return;
        }

        $cartService = app(\App\Services\CartService::class);
        $cartItems = $cartService->getItems();

        if (empty($cartItems)) {
            \Illuminate\Support\Facades\Log::warning('SelectShipping: cartItems is empty!');
            return;
        }

        $biteshipService = app(BiteshipService::class);
        $this->rates = $biteshipService->getShippingRates(
            $address->area_id,
            $cartItems,
            null,
            $address->latitude ? (float) $address->latitude : null,
            $address->longitude ? (float) $address->longitude : null
        );
        
        // Auto-select first available rate if possible
        if (count($this->rates) > 0 && !$this->selectedRate) {
            $firstRate = $this->rates[0];
            $value = $firstRate['courier_code'] . '|' . $firstRate['courier_service_code'] . '|' . $firstRate['price'];
            $this->updatedSelectedRate($value);
        }
    }

    public function updatedSelectedRate($value)
    {
        $this->selectedRate = $value;

        // Tell the checkout flow when this courier can actually collect the
        // parcel, so it can warn before payment if the chosen service is already
        // past its pickup cutoff (empty string means it can be collected now).
        [$courier, $service] = array_pad(explode('|', (string) $value), 3, null);
        $opensAt = \App\Support\CourierSchedule::nextOpening($courier, $service);
        $pickupOpensAt = $opensAt
            ? $opensAt->translatedFormat('l, d M').' pukul '.$opensAt->format('H:i').' WIB'
            : '';

        $this->dispatch('shippingRateSelected', selection: $value, pickupOpensAt: $pickupOpensAt);
    }

    public function render()
    {
        return view('livewire.select-shipping');
    }
}

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

        if (Auth::check()) {
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
        } elseif (! empty(session('checkout.guest_address.area_id'))) {
            // Guest with an address already stashed in the session: quote from it.
            $this->fetchRates();
        }
    }

    #[On('addressSelected')]
    public function handleAddressSelected($addressId)
    {
        $this->selectedAddressId = $addressId;
        $this->fetchRates();
    }

    // A guest saved/changed their session address; re-quote couriers.
    #[On('guestAddressUpdated')]
    public function handleGuestAddressUpdated()
    {
        $this->fetchRates();
    }

    /**
     * The shipping destination: either the chosen DB address (logged-in) or the
     * guest's session address. Returns [areaId, lat, lng] or null.
     */
    protected function destination(): ?array
    {
        if ($this->selectedAddressId) {
            $address = Address::find($this->selectedAddressId);

            if (! $address || empty($address->area_id)) {
                return null;
            }

            return [
                $address->area_id,
                $address->latitude ? (float) $address->latitude : null,
                $address->longitude ? (float) $address->longitude : null,
            ];
        }

        // Guest: read the address stashed by GuestAddressForm.
        $guest = session('checkout.guest_address');
        if (is_array($guest) && ! empty($guest['area_id'])) {
            return [
                $guest['area_id'],
                isset($guest['latitude']) ? (float) $guest['latitude'] : null,
                isset($guest['longitude']) ? (float) $guest['longitude'] : null,
            ];
        }

        return null;
    }

    public function fetchRates()
    {
        $this->rates = [];
        $this->selectedRate = null;
        $this->hasValidArea = true;

        $destination = $this->destination();

        if ($destination === null) {
            // Only flag an invalid area when the shopper actually has an address
            // that lacks a serviceable area; an empty guest form is just "not yet".
            $this->hasValidArea = ! ($this->selectedAddressId || ! empty(session('checkout.guest_address.area_id')));

            return;
        }

        [$areaId, $lat, $lng] = $destination;

        $cartService = app(\App\Services\CartService::class);
        $cartItems = $cartService->getItems();

        if (empty($cartItems)) {
            \Illuminate\Support\Facades\Log::warning('SelectShipping: cartItems is empty!');
            return;
        }

        $biteshipService = app(BiteshipService::class);
        $this->rates = $biteshipService->getShippingRates(
            $areaId,
            $cartItems,
            null,
            $lat,
            $lng
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
        // For a guest there is no selectedAddressId (rates are quoted from the
        // session address), so the view must not key its empty state on it alone.
        $hasAddress = (bool) ($this->selectedAddressId || ! empty(session('checkout.guest_address.area_id')));

        return view('livewire.select-shipping', compact('hasAddress'));
    }
}

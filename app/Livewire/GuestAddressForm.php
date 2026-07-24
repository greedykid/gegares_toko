<?php

namespace App\Livewire;

use App\Services\BiteshipService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

/**
 * Address capture for a not-yet-authenticated shopper.
 *
 * Mirrors the fields and the area/landmark autocomplete of ManageAddresses, but
 * there is no user to attach a row to yet, so "Simpan" only stashes the address
 * in the session (`checkout.guest_address`). It is materialised into a real
 * Address — and registered with Biteship — later, in CheckoutController::resume,
 * once the shopper has logged in. SelectShipping reads the same session key to
 * quote couriers, so the guest still sees live shipping rates.
 */
class GuestAddressForm extends Component
{
    protected string $sessionKey = 'checkout.guest_address';

    // Once an address is stashed we show a summary card instead of the form.
    public bool $saved = false;

    // Form fields (same set the authenticated form collects).
    public $label = 'Rumah';
    public $recipient_name = '';
    public $phone = '';
    public $area_id = '';
    public $area_name = '';
    public $city = '';
    public $province = '';
    public $postal_code = '';
    public $address_line = '';
    public $latitude = -6.2000000;
    public $longitude = 106.8166660;

    // Autocomplete state.
    public $searchQuery = '';
    public $areaSearchResults = [];
    public $addressSearchQuery = '';
    public $addressSearchResults = [];

    public function mount(): void
    {
        $saved = session($this->sessionKey);

        if (is_array($saved) && ! empty($saved['area_id'])) {
            $this->fill(array_intersect_key($saved, array_flip([
                'label', 'recipient_name', 'phone', 'area_id', 'area_name',
                'city', 'province', 'postal_code', 'address_line', 'latitude', 'longitude',
            ])));
            $this->saved = true;
        }
    }

    public function updatedSearchQuery(): void
    {
        if (strlen($this->searchQuery) > 2) {
            $this->areaSearchResults = app(BiteshipService::class)->searchArea($this->searchQuery.' Jakarta');
        } else {
            $this->areaSearchResults = [];
        }
    }

    public function selectArea($id, $name, $city, $province, $postalCode, $latitude = null, $longitude = null): void
    {
        $this->area_id = $id;
        $this->area_name = $name;
        $this->searchQuery = $name.', '.$city;
        $this->city = $city;
        $this->province = $province;
        $this->postal_code = $postalCode;
        if ($latitude && $longitude) {
            $this->latitude = (float) $latitude;
            $this->longitude = (float) $longitude;
        }
        $this->areaSearchResults = [];
        $this->addressSearchResults = [];
    }

    public function updatedAddressSearchQuery(): void
    {
        // The landmark search only makes sense once a kecamatan is chosen; its
        // results are scoped to that area (same funnel as ManageAddresses).
        if (empty($this->area_id) || strlen($this->addressSearchQuery) <= 3) {
            $this->addressSearchResults = [];

            return;
        }

        $context = collect([$this->area_name, $this->city, $this->province])->filter()->implode(', ');
        $fullQuery = trim($this->addressSearchQuery).($context ? ', '.$context : '');

        $isDefaultCentre = (float) $this->latitude === -6.2 && (float) $this->longitude === 106.816666;
        $hasCoords = $this->latitude && $this->longitude && ! $isDefaultCentre;
        $delta = 0.04;
        $viewbox = $hasCoords
            ? ($this->longitude - $delta).','.($this->latitude + $delta).','.($this->longitude + $delta).','.($this->latitude - $delta)
            : '106.685,-6.107,106.973,-6.370';

        $cacheKey = 'address_search_'.md5($fullQuery.'|'.$viewbox);

        $this->addressSearchResults = Cache::remember($cacheKey, 3600, function () use ($fullQuery, $viewbox) {
            $response = Http::withHeaders([
                'User-Agent' => 'GegaresEcommerce/1.0 (contact@gegares.com)',
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $fullQuery,
                'format' => 'json',
                'viewbox' => $viewbox,
                'bounded' => 1,
                'limit' => 5,
                'addressdetails' => 1,
            ]);

            return $response->successful() ? $response->json() : [];
        });
    }

    public function selectAddressResult($displayName, $lat, $lon): void
    {
        $this->address_line = $displayName;
        $this->latitude = (float) $lat;
        $this->longitude = (float) $lon;
        $this->addressSearchQuery = $displayName;
        $this->addressSearchResults = [];
    }

    public function save(): void
    {
        $this->validate([
            'label' => 'required|string|max:100',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'area_id' => 'required|string',
            'city' => 'required|string|max:255',
            'province' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'address_line' => 'required|string',
        ], [
            'area_id.required' => 'Silakan pilih area/kecamatan dari daftar pencarian.',
        ]);

        session([$this->sessionKey => [
            'label' => $this->label,
            'recipient_name' => $this->recipient_name,
            'phone' => $this->phone,
            'area_id' => $this->area_id,
            'area_name' => $this->area_name,
            'city' => $this->city,
            'province' => $this->province,
            'postal_code' => $this->postal_code,
            'address_line' => $this->address_line,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_primary' => true,
        ]]);

        $this->saved = true;

        // Tell SelectShipping to quote couriers from the freshly saved address.
        $this->dispatch('guestAddressUpdated');
    }

    public function edit(): void
    {
        $this->saved = false;
    }

    public function render()
    {
        return view('livewire.guest-address-form');
    }
}

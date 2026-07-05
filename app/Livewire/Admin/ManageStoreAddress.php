<?php

namespace App\Livewire\Admin;

use App\Models\StoreSetting;
use App\Services\BiteshipService;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class ManageStoreAddress extends Component
{
    public $store_name;
    public $contact_phone;
    public $contact_email;
    public $address_line;
    public $area_id;
    public $city;
    public $province;
    public $postal_code;
    public $latitude;
    public $longitude;
    public $biteship_location_id;

    // Autocomplete state
    public $searchQuery = '';
    public $areaSearchResults = [];
    public $addressSearchQuery = '';
    public $addressSearchResults = [];

    public function mount()
    {
        $setting = \App\Models\StoreSetting::orderBy('id')->first();

        if ($setting) {
            $this->store_name = $setting->store_name;
            $this->contact_phone = $setting->contact_phone;
            $this->contact_email = $setting->contact_email;
            $this->address_line = $setting->address_line;
            $this->area_id = $setting->area_id;
            $this->city = $setting->city;
            $this->province = $setting->province;
            $this->postal_code = $setting->postal_code;
            $this->latitude = $setting->latitude;
            $this->longitude = $setting->longitude;
            $this->biteship_location_id = $setting->biteship_location_id;
            $this->searchQuery = $this->city . ', ' . $this->province;
        } else {
            $this->store_name = 'Gegares Ecommerce';
            $this->latitude = -6.1558;
            $this->longitude = 106.8048;
        }
    }

    public function updatedAddressSearchQuery()
    {
        if (strlen($this->addressSearchQuery) > 3) {
            $cacheKey = 'address_search_' . md5($this->addressSearchQuery);
            $this->addressSearchResults = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function() {
                $response = Http::withHeaders([
                    'User-Agent' => 'GegaresEcommerce/1.0'
                ])->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $this->addressSearchQuery,
                    'format' => 'json',
                    'limit' => 5,
                    'addressdetails' => 1
                ]);

                return $response->successful() ? $response->json() : [];
            });
        } else {
            $this->addressSearchResults = [];
        }
    }

    public function selectAddressResult($displayName, $lat, $lon)
    {
        $this->address_line = $displayName;
        $this->latitude = (float) $lat;
        $this->longitude = (float) $lon;
        $this->addressSearchQuery = $displayName;
        $this->addressSearchResults = [];
    }

    public function updatedSearchQuery()
    {
        if (strlen($this->searchQuery) > 2) {
            $biteshipService = app(BiteshipService::class);
            $this->areaSearchResults = $biteshipService->searchArea($this->searchQuery);
        } else {
            $this->areaSearchResults = [];
        }
    }

    public function selectArea($id, $name, $city, $province, $postalCode, $lat = null, $lng = null)
    {
        $this->area_id = $id;
        $this->city = $city;
        $this->province = $province;
        $this->postal_code = $postalCode;
        $this->searchQuery = $name . ', ' . $city;
        if ($lat && $lng) {
            $this->latitude = (float) $lat;
            $this->longitude = (float) $lng;
        }
        $this->areaSearchResults = [];
    }

    public function save(BiteshipService $biteshipService)
    {
        $this->validate([
            'store_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'address_line' => 'required|string',
            'area_id' => 'required|string',
            'city' => 'required|string',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $payload = [
            'name' => 'Store Pickup',
            'contact_name' => $this->store_name,
            'contact_phone' => $this->contact_phone,
            'address' => $this->address_line . ', ' . $this->city . ', ' . $this->province,
            'postal_code' => $this->postal_code ?: '12345',
            'latitude' => (string) $this->latitude,
            'longitude' => (string) $this->longitude,
            'type' => 'origin'
        ];

        // API Integration
        try {
            if ($this->biteship_location_id) {
                $biteshipService->updateLocation($this->biteship_location_id, $payload);
            } else {
                $newLocId = $biteshipService->createLocation($payload);
                if ($newLocId) {
                    $this->biteship_location_id = $newLocId;
                }
            }
        } catch (\Exception $e) {
            // Log error but continue saving local data fallback
            \Illuminate\Support\Facades\Log::error('Biteship sync error: ' . $e->getMessage());
        }

        // Database Persistence
        $setting = \App\Models\StoreSetting::firstOrNew([]);
        $setting->store_name = $this->store_name;
        $setting->contact_phone = $this->contact_phone;
        $setting->contact_email = $this->contact_email;
        $setting->address_line = $this->address_line;
        $setting->area_id = $this->area_id;
        $setting->city = $this->city;
        $setting->province = $this->province;
        $setting->postal_code = $this->postal_code;
        $setting->latitude = $this->latitude;
        $setting->longitude = $this->longitude;
        $setting->biteship_location_id = $this->biteship_location_id;
        $setting->save();

        $this->dispatch('toast', message: 'Pengaturan toko berhasil diperbarui.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.manage-store-address');
    }
}

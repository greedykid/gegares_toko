<?php

namespace App\Livewire;

use App\Models\Address;
use App\Services\BiteshipService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ManageAddresses extends Component
{
    public $addresses;
    public $selectedAddressId = null;

    // Modal state
    public $showModal = false;
    public $isEditing = false;
    public $editId = null;
    public $showDeleteModal = false;
    public $addressIdToDelete = null;

    // Form fields
    public $label = 'Rumah';
    public $recipient_name = '';
    public $phone = '';
    public $area_id = '';
    public $city = '';
    public $province = '';
    public $postal_code = '';
    public $address_line = '';
    public $is_primary = false;
    public $latitude = -6.2000000;
    public $longitude = 106.8166660;

    // Autocomplete state
    public $searchQuery = '';
    public $areaSearchResults = [];
    public $addressSearchQuery = '';
    public $addressSearchResults = [];

    public function mount($selectedAddressId = null)
    {
        $this->loadAddresses();
        $this->selectedAddressId = $selectedAddressId ?? (optional($this->addresses->first())->id ?? null);
        
        if ($this->selectedAddressId) {
            $this->dispatch('addressSelected', addressId: $this->selectedAddressId);
        }
    }

    public function updatedSelectedAddressId($value)
    {
        $this->dispatch('addressSelected', addressId: $value);
    }

    public function loadAddresses()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->addresses = $user->addresses()->orderByDesc('is_primary')->get();
    }

    public function updatedAddressSearchQuery()
    {
        if (strlen($this->addressSearchQuery) > 3) {
            $response = Http::withHeaders([
                'User-Agent' => 'GegaresEcommerce/1.0 (contact@gegares.com)'
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $this->addressSearchQuery,
                'format' => 'json',
                'viewbox' => '106.685,-6.107,106.973,-6.370',
                'bounded' => 1,
                'limit' => 5,
                'addressdetails' => 1
            ]);

            if ($response->successful()) {
                $this->addressSearchResults = $response->json();
            }
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
            // Append " Jakarta" to help the API narrow down results, 
            // though we still filter strictly in the Service.
            $this->areaSearchResults = $biteshipService->searchArea($this->searchQuery . ' Jakarta');
        } else {
            $this->areaSearchResults = [];
        }
    }

    public function selectArea($id, $name, $city, $province, $postalCode, $latitude = null, $longitude = null)
    {
        $this->area_id = $id;
        $this->searchQuery = $name . ', ' . $city;
        $this->city = $city;
        $this->province = $province;
        $this->postal_code = $postalCode;
        if ($latitude && $longitude) {
            $this->latitude = (float) $latitude;
            $this->longitude = (float) $longitude;
        }
        $this->areaSearchResults = [];
    }

    public function createNew()
    {
        $this->resetFields();
        $this->isEditing = false;
        $this->showModal = true;
        
        // Auto-set as primary if it's their first address
        if (!$this->addresses || $this->addresses->count() === 0) {
            $this->is_primary = true;
        }
    }

    public function editAddress($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $address = Address::where('user_id', $user->id)->findOrFail($id);
        $this->editId = $address->id;
        $this->label = $address->label;
        $this->recipient_name = $address->recipient_name;
        $this->phone = $address->phone;
        $this->area_id = $address->area_id ?? '';
        $this->city = $address->city;
        $this->province = $address->province ?? '';
        $this->postal_code = $address->postal_code ?? '';
        $this->address_line = $address->address_line ?? $address->full_address;
        $this->latitude = $address->latitude ?? -6.2000000;
        $this->longitude = $address->longitude ?? 106.8166660;
        
        $this->searchQuery = $this->city . ($this->province ? ', ' . $this->province : '');

        $this->is_primary = $address->is_primary;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(BiteshipService $biteshipService)
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

        if ($this->is_primary) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $user->addresses()->update(['is_primary' => false]);
        }
        
        $locationPayload = [
            'name' => $this->label,
            'contact_name' => $this->recipient_name,
            'contact_phone' => $this->phone,
            'address' => $this->address_line . ', ' . $this->city . ', ' . $this->province,
            'postal_code' => $this->postal_code ?: '12345',
            'latitude' => (string) $this->latitude,
            'longitude' => (string) $this->longitude,
            'type' => 'destination'
        ];

        if ($this->isEditing) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $address = Address::where('user_id', $user->id)->findOrFail($this->editId);
            
            if ($address->biteship_location_id) {
                $biteshipService->updateLocation($address->biteship_location_id, $locationPayload);
            } else {
                $address->biteship_location_id = $biteshipService->createLocation($locationPayload);
            }

            $address->update([
                'label' => $this->label,
                'recipient_name' => $this->recipient_name,
                'phone' => $this->phone,
                'area_id' => $this->area_id,
                'city' => $this->city,
                'province' => $this->province,
                'postal_code' => $this->postal_code,
                'address_line' => $this->address_line,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'is_primary' => $this->is_primary,
            ]);
        } else {
            $biteshipLocationId = $biteshipService->createLocation($locationPayload);

            /** @var \App\Models\User $user */
            $user = Auth::user();
            $address = $user->addresses()->create([
                'label' => $this->label,
                'recipient_name' => $this->recipient_name,
                'phone' => $this->phone,
                'area_id' => $this->area_id,
                'city' => $this->city,
                'province' => $this->province,
                'postal_code' => $this->postal_code,
                'address_line' => $this->address_line,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'is_primary' => $this->is_primary,
                'biteship_location_id' => $biteshipLocationId,
            ]);
            
            $this->selectedAddressId = $address->id;
            $this->dispatch('addressSelected', addressId: $address->id);
        }

        $this->showModal = false;
        $this->resetFields();
        $this->loadAddresses();
    }

    public function setPrimary($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->addresses()->update(['is_primary' => false]);
        Address::where('user_id', $user->id)->where('id', $id)->update(['is_primary' => true]);
        
        $this->loadAddresses();
    }

    public function confirmDelete($id)
    {
        $this->addressIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteAddress($id = null, BiteshipService $biteshipService = null)
    {
        $id = $id ?? $this->addressIdToDelete;
        if (!$id) return;

        if (!$biteshipService) {
            $biteshipService = app(BiteshipService::class);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $address = Address::where('user_id', $user->id)->where('id', $id)->first();
        
        if ($address) {
            if ($address->biteship_location_id) {
                $biteshipService->deleteLocation($address->biteship_location_id);
            }
            $address->delete();
        }
        
        $this->loadAddresses();
        if ($this->selectedAddressId == $id) {
            $this->selectedAddressId = optional($this->addresses->first())->id ?? null;
        }

        $this->showDeleteModal = false;
        $this->addressIdToDelete = null;
    }

    public function resetFields()
    {
        $this->label = 'Rumah';
        $this->recipient_name = '';
        $this->phone = '';
        $this->area_id = '';
        $this->city = '';
        $this->province = '';
        $this->postal_code = '';
        $this->address_line = '';
        $this->is_primary = false;
        $this->latitude = -6.2000000;
        $this->longitude = 106.8166660;
        $this->editId = null;
        $this->searchQuery = '';
        $this->addressSearchQuery = '';
        $this->addressSearchResults = [];
    }

    public function render()
    {
        return view('livewire.manage-addresses');
    }
}

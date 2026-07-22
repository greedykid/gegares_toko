<?php

namespace App\Livewire;

use App\Models\Address;
use App\Services\BiteshipService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
    // The chosen area/kecamatan name, kept so the landmark search can be scoped
    // to it (results funnel down from the area picked above).
    public $area_name = '';

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
        // Funnel: the landmark search is only meaningful once an area/kecamatan
        // is chosen — its results are scoped to that area.
        if (empty($this->area_id) || strlen($this->addressSearchQuery) <= 3) {
            $this->addressSearchResults = [];

            return;
        }

        // Narrow the query to the selected area by appending its
        // kecamatan/city/province, so "Jl. Mangga" resolves within the chosen
        // kecamatan instead of every Mangga street in Jakarta.
        $context = collect([$this->area_name, $this->city, $this->province])
            ->filter()
            ->implode(', ');
        $fullQuery = trim($this->addressSearchQuery) . ($context ? ', ' . $context : '');

        // When the area gave us real coordinates, bound the search to a ~4km box
        // around them; otherwise fall back to the Jakarta-wide box.
        $isDefaultCentre = (float) $this->latitude === -6.2 && (float) $this->longitude === 106.816666;
        $hasCoords = $this->latitude && $this->longitude && ! $isDefaultCentre;
        $delta = 0.04;
        $viewbox = $hasCoords
            ? ($this->longitude - $delta) . ',' . ($this->latitude + $delta) . ',' . ($this->longitude + $delta) . ',' . ($this->latitude - $delta)
            : '106.685,-6.107,106.973,-6.370';

        // Cache key includes the area scope so results for different kecamatan
        // never collide.
        $cacheKey = 'address_search_' . md5($fullQuery . '|' . $viewbox);

        $this->addressSearchResults = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($fullQuery, $viewbox) {
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
        $this->area_name = $name;
        $this->searchQuery = $name . ', ' . $city;
        $this->city = $city;
        $this->province = $province;
        $this->postal_code = $postalCode;
        if ($latitude && $longitude) {
            $this->latitude = (float) $latitude;
            $this->longitude = (float) $longitude;
        }
        $this->areaSearchResults = [];
        // Picking a new area invalidates any landmark results scoped to the old
        // one; clear them so the next search re-scopes cleanly.
        $this->addressSearchResults = [];
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

        /** @var \App\Models\User $user */
        $user = Auth::user();

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

        // Shared columns; is_primary is written inside the same transaction that
        // clears the other primaries.
        $fields = [
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
        ];

        if ($this->isEditing) {
            $address = Address::where('user_id', $user->id)->findOrFail($this->editId);

            // Biteship location sync is network I/O — do it before the DB
            // transaction so a slow API call never holds a write lock open.
            if ($address->biteship_location_id) {
                $biteshipService->updateLocation($address->biteship_location_id, $locationPayload);
                $fields['biteship_location_id'] = $address->biteship_location_id;
            } else {
                $fields['biteship_location_id'] = $biteshipService->createLocation($locationPayload);
            }

            // Clearing the other primaries and writing this address must be
            // atomic — a failure between them used to leave the account with no
            // primary address at all.
            DB::transaction(function () use ($user, $address, $fields) {
                if ($this->is_primary) {
                    $user->addresses()->where('id', '!=', $address->id)->update(['is_primary' => false]);
                }

                $address->update($fields);
            });
        } else {
            $fields['biteship_location_id'] = $biteshipService->createLocation($locationPayload);

            $address = DB::transaction(function () use ($user, $fields) {
                if ($this->is_primary) {
                    $user->addresses()->update(['is_primary' => false]);
                }

                return $user->addresses()->create($fields);
            });

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

        // Atomic so a failure between the two writes can't leave the account
        // with no primary address.
        DB::transaction(function () use ($user, $id) {
            $user->addresses()->update(['is_primary' => false]);
            $user->addresses()->where('id', $id)->update(['is_primary' => true]);
        });

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
        $this->area_name = '';
        $this->addressSearchQuery = '';
        $this->addressSearchResults = [];
        $this->areaSearchResults = [];
    }

    public function render()
    {
        return view('livewire.manage-addresses');
    }
}

<?php

namespace App\Services;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Persisting a delivery address for a customer.
 *
 * Extracted from ManageAddresses::save() so the same three steps — register the
 * location with Biteship, keep at most one primary, write the row atomically —
 * can also run when a guest's session address is materialised after they log in
 * at the end of checkout.
 */
class AddressService
{
    public function __construct(protected BiteshipService $biteship) {}

    /**
     * Create a new address for the user. Expects the same field set the address
     * form collects: label, recipient_name, phone, area_id, city, province,
     * postal_code, address_line, latitude, longitude, is_primary.
     */
    public function create(User $user, array $data): Address
    {
        // Network I/O first, outside the transaction, so a slow Biteship call
        // never holds a write lock open.
        $locationId = $this->biteship->createLocation([
            'name' => $data['label'] ?? 'Rumah',
            'contact_name' => $data['recipient_name'] ?? '',
            'contact_phone' => $data['phone'] ?? '',
            'address' => trim(($data['address_line'] ?? '').', '.($data['city'] ?? '').', '.($data['province'] ?? '')),
            'postal_code' => $data['postal_code'] ?: '12345',
            'latitude' => (string) ($data['latitude'] ?? ''),
            'longitude' => (string) ($data['longitude'] ?? ''),
            'type' => 'destination',
        ]);

        $fields = [
            'label' => $data['label'] ?? 'Rumah',
            'recipient_name' => $data['recipient_name'] ?? '',
            'phone' => $data['phone'] ?? '',
            'area_id' => $data['area_id'] ?? '',
            'city' => $data['city'] ?? '',
            'province' => $data['province'] ?? '',
            'postal_code' => $data['postal_code'] ?? '',
            'address_line' => $data['address_line'] ?? '',
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'is_primary' => (bool) ($data['is_primary'] ?? false),
            'biteship_location_id' => $locationId,
        ];

        return DB::transaction(function () use ($user, $fields) {
            // Clearing the other primaries and writing this address must be
            // atomic — a failure between them would leave no primary at all.
            if ($fields['is_primary']) {
                $user->addresses()->update(['is_primary' => false]);
            }

            return $user->addresses()->create($fields);
        });
    }
}

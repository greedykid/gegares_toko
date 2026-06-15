<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Order;
use App\Models\User;
use App\Models\StoreSetting;

class BiteshipService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('biteship.base_url');
        $this->apiKey = config('biteship.api_key');
        $this->timeout = config('biteship.timeout', 30);
    }

    public function searchArea(string $query): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/maps/areas", [
                    'countries' => 'ID',
                    'input' => $query,
                    'type' => 'single',
                ]);

            if ($response->successful()) {
                $areas = $response->json('areas', []);
                
                // Filter only for DKI Jakarta
                return collect($areas)->filter(function ($area) {
                    $province = $area['administrative_division_level_1_name'] ?? '';
                    return stripos($province, 'DKI Jakarta') !== false;
                })->values()->toArray();
            }

            Log::warning('Biteship searchArea failed: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('Biteship searchArea error: ' . $e->getMessage());
            return [];
        }
    }

    public function getShippingRates(string $destinationAreaId, array $items, ?string $originAreaId = null, ?float $destLat = null, ?float $destLng = null): array
    {
        try {
            $origin = $originAreaId ?? config('biteship.origin_area_id');

            $itemsPayload = collect($items)->map(function ($item) {
                return [
                    'name' => $item['name'] ?? 'Jajanan Pasar',
                    'description' => $item['description'] ?? '',
                    'value' => (int) ($item['price'] ?? 0),
                    'quantity' => (int) ($item['quantity'] ?? 1),
                    'weight' => (int) ($item['weight'] ?? 200),
                    'length' => (int) ($item['length'] ?? 15),
                    'width' => (int) ($item['width'] ?? 15),
                    'height' => (int) ($item['height'] ?? 10),
                ];
            })->values()->toArray();

            $payload = [
                'origin_latitude' => -6.200000,
                'origin_longitude' => 106.816666,
                'destination_latitude' => $destLat ?? -6.200000,
                'destination_longitude' => $destLng ?? 106.816666,
                'origin_area_id' => $origin,
                'destination_area_id' => $destinationAreaId,
                'couriers' => 'gojek,grab,rpx,anteraja,paxel,borzo,lalamove,dash_express',
                'items' => $itemsPayload,
            ];

            $cacheKey = 'biteship_rates_' . md5(json_encode($payload));

            return Cache::remember($cacheKey, 300, function () use ($payload) {
                Log::info('Biteship Rates Payload: ' . json_encode($payload));
                
                $response = Http::withToken($this->apiKey)
                    ->timeout($this->timeout)
                    ->post("{$this->baseUrl}/rates/couriers", $payload);

                if ($response->successful()) {
                    $rates = $response->json('pricing', []);

                    // Filter only Instant and Sameday
                    return collect($rates)->filter(function ($rate) {
                        $type = strtolower($rate['type'] ?? '');
                        return in_array($type, ['instant', 'same_day', 'sameday']);
                    })->values()->toArray();
                }

                Log::warning('Biteship getShippingRates failed: ' . $response->body());
                return [];
            });
        } catch (\Exception $e) {
            Log::error('Biteship getShippingRates error: ' . $e->getMessage());
            return [];
        }
    }

    public function trackShipment(string $trackingId, string $courierId): ?array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/trackings/{$trackingId}/couriers/{$courierId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Biteship trackShipment error: ' . $e->getMessage());
            return null;
        }
    }

    public function createLocation(array $data): ?string
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/locations", $data);

            if ($response->successful()) {
                return $response->json('id');
            }

            Log::warning('Biteship createLocation failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Biteship createLocation error: ' . $e->getMessage());
            return null;
        }
    }

    public function updateLocation(string $id, array $data): bool
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/locations/{$id}", $data);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Biteship updateLocation failed: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('Biteship updateLocation error: ' . $e->getMessage());
            return false;
        }
    }

    public function createOrder(Order $order): ?array
    {
        try {
            if (empty($this->apiKey)) {
                return ['error' => 'API Key Biteship belum dikonfigurasi di file .env (BITESHIP_API_KEY).'];
            }

            if (!$order->address) {
                return ['error' => 'Alamat pengiriman tidak ditemukan untuk pesanan ini.'];
            }

            if (empty($order->address->latitude) || empty($order->address->longitude)) {
                return ['error' => 'Koordinat alamat pengiriman (latitude/longitude) belum ditentukan. Silakan perbarui koordinat pada alamat pengiriman terlebih dahulu.'];
            }

            // Fetch store profile for shipper data
            $setting = StoreSetting::first();

            $shipper = [
                'name' => ($setting && $setting->store_name) ? $setting->store_name : 'Admin Gegares',
                'phone' => ($setting && $setting->contact_phone) ? $setting->contact_phone : '08219293812',
                'email' => ($setting && $setting->contact_email) ? $setting->contact_email : 'admin@gegares.com',
                'organization' => 'Gegares',
                'address' => ($setting && $setting->address_line) ? $setting->address_line . ', ' . $setting->city . ', ' . $setting->province : 'Jl. Jembatan Besi II No. 1, Jakarta Barat',
                'postal_code' => ($setting && $setting->postal_code) ? (int) $setting->postal_code : 11320,
                'coordinate' => [
                    'latitude' => ($setting && $setting->latitude) ? (float) $setting->latitude : -6.1558,
                    'longitude' => ($setting && $setting->longitude) ? (float) $setting->longitude : 106.8048,
                ],
            ];

            $destination = [
                'contact_name' => $order->address->recipient_name,
                'contact_phone' => $order->address->phone,
                'contact_email' => $order->user->email,
                'address' => $order->address->address_line . ', ' . $order->address->city . ', ' . $order->address->province,
                'postal_code' => (int) $order->address->postal_code,
                'coordinate' => [
                    'latitude' => (float) $order->address->latitude,
                    'longitude' => (float) $order->address->longitude,
                ],
                'note' => $order->notes ?? '',
            ];

            $items = $order->items->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'description' => $item->product_name,
                    'value' => (int) $item->product_price,
                    'quantity' => (int) $item->quantity,
                    'weight' => (int) ($item->product->weight ?? 200),
                    'height' => (int) ($item->product->height ?? 10),
                    'width' => (int) ($item->product->width ?? 15),
                    'length' => (int) ($item->product->length ?? 15),
                ];
            })->toArray();

            $courierCompany = strtolower($order->shipping_courier);
            $courierType = strtolower($order->shipping_service);

            $deliveryType = 'now';
            $deliveryDate = null;
            $deliveryTime = null;

            // Check if it's a same day service (Grab/Gojek sameday) and check if we are outside the service hours
            $isSameDay = in_array($courierCompany, ['grab', 'gojek']) && in_array($courierType, ['same_day', 'sameday']);
            
            if ($isSameDay) {
                $now = now()->timezone('Asia/Jakarta');
                $hour = (int)$now->format('H');
                
                // Grab Same Day: 09:00 - 14:00, Gojek Same Day: 09:00 - 15:00
                $maxHour = ($courierCompany === 'grab') ? 14 : 15;
                
                if ($hour >= $maxHour || $hour < 9) {
                    $deliveryType = 'scheduled';
                    
                    if ($hour >= $maxHour) {
                        // Schedule for tomorrow at 09:00
                        $scheduledDate = $now->copy()->addDay();
                    } else {
                        // Schedule for today at 09:00
                        $scheduledDate = $now->copy();
                    }
                    
                    $deliveryDate = $scheduledDate->format('Y-m-d');
                    $deliveryTime = '09:00';
                }
            }

            $payload = [
                'reference_id' => $order->order_number,
                'shipper_contact_name' => $shipper['name'],
                'shipper_contact_phone' => $shipper['phone'],
                'shipper_contact_email' => $shipper['email'],
                'shipper_organization' => $shipper['organization'],
                'shipper_address' => $shipper['address'],
                'shipper_coordinate' => $shipper['coordinate'],
                'shipper_postal_code' => $shipper['postal_code'],
                'origin_contact_name' => $shipper['name'],
                'origin_contact_phone' => $shipper['phone'],
                'origin_address' => $shipper['address'],
                'origin_coordinate' => $shipper['coordinate'],
                'origin_postal_code' => $shipper['postal_code'],
                'destination_contact_name' => $destination['contact_name'],
                'destination_contact_phone' => $destination['contact_phone'],
                'destination_contact_email' => $destination['contact_email'],
                'destination_address' => $destination['address'],
                'destination_coordinate' => $destination['coordinate'],
                'destination_postal_code' => $destination['postal_code'],
                'destination_note' => $destination['note'],
                'courier_company' => $courierCompany,
                'courier_type' => $courierType,
                'delivery_type' => $deliveryType,
                'order_note' => "Order #{$order->order_number}",
                'items' => $items,
            ];

            if ($deliveryType === 'scheduled') {
                $payload['delivery_date'] = $deliveryDate;
                $payload['delivery_time'] = $deliveryTime;
            }

            Log::info('Biteship Create Order Payload: ' . json_encode($payload));

            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/orders", $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Store the Biteship Order ID and Tracking ID for future reliable webhooks and links
                if (isset($data['id'])) {
                    $order->update([
                        'biteship_order_id' => $data['id'],
                        'courier_tracking_id' => $data['courier_tracking_id'] ?? null
                    ]);
                }
                
                // Ensure success field is set
                if (!isset($data['success'])) {
                    $data['success'] = true;
                }
                
                return $data;
            }

            Log::warning('Biteship createOrder failed: ' . $response->body());
            
            $errorMessage = $response->json('error') 
                ?? $response->json('message') 
                ?? ($response->json('errors') ? implode(', ', (array) $response->json('errors')) : null)
                ?? 'Unknown error from Biteship (HTTP Status ' . $response->status() . ')';

            return ['error' => $errorMessage];
        } catch (\Exception $e) {
            Log::error('Biteship createOrder error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function getOrder(string $biteshipOrderId): ?array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/orders/{$biteshipOrderId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Biteship getOrder failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Biteship getOrder error: ' . $e->getMessage());
            return null;
        }
    }

    public function deleteLocation(string $id): bool
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->delete("{$this->baseUrl}/locations/{$id}");

            if ($response->successful()) {
                return true;
            }

            Log::warning('Biteship deleteLocation failed: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('Biteship deleteLocation error: ' . $e->getMessage());
            return false;
        }
    }
}

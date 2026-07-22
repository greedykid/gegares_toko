<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StoreSetting;
use App\Models\User;
use App\Support\CourierSchedule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        // Area lookups are static reference data; cache successful results so
        // typing in the address autocomplete doesn't hit the API every keystroke.
        $cacheKey = 'biteship_area_'.md5(mb_strtolower(trim($query)));
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

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
                $result = collect($areas)->filter(function ($area) {
                    $province = $area['administrative_division_level_1_name'] ?? '';

                    return stripos($province, 'DKI Jakarta') !== false;
                })->values()->toArray();

                // Only cache successful lookups (never cache API failures).
                Cache::put($cacheKey, $result, now()->addDay());

                return $result;
            }

            Log::warning('Biteship searchArea failed: '.$response->body());

            return [];
        } catch (\Exception $e) {
            Log::error('Biteship searchArea error: '.$e->getMessage());

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

            $cacheKey = 'biteship_rates_'.md5(json_encode($payload));

            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }

            Log::info('Biteship Rates Payload: '.json_encode($payload));

            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/rates/couriers", $payload);

            if (! $response->successful()) {
                // Deliberately NOT cached. This used to run inside
                // Cache::remember(), which stored the empty result like any
                // other — so one error response from Biteship kept checkout
                // broken for this cart for five more minutes after Biteship had
                // already recovered.
                Log::warning('Biteship getShippingRates failed: '.$response->body());

                return [];
            }

            // Filter only Instant and Sameday
            $rates = collect($response->json('pricing', []))
                ->filter(function ($rate) {
                    $type = strtolower($rate['type'] ?? '');

                    return in_array($type, ['instant', 'same_day', 'sameday']);
                })->values()->toArray();

            // An empty list from a call that succeeded is a real answer — nothing
            // serves this address — so that one is worth remembering.
            Cache::put($cacheKey, $rates, 300);

            return $rates;
        } catch (\Exception $e) {
            Log::error('Biteship getShippingRates error: '.$e->getMessage());

            return [];
        }
    }

    public function trackShipment(string $trackingId, string $courierId): ?array
    {
        // Short-lived cache so repeated tracking polls (user + admin) don't hit
        // the API every few seconds; webhooks still update status in real time.
        $cacheKey = 'biteship_track_'.$trackingId.'_'.strtolower($courierId);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/trackings/{$trackingId}/couriers/{$courierId}");

            if ($response->successful()) {
                $data = $response->json();
                Cache::put($cacheKey, $data, 60);

                return $data;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Biteship trackShipment error: '.$e->getMessage());

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

            Log::warning('Biteship createLocation failed: '.$response->body());

            return null;
        } catch (\Exception $e) {
            Log::error('Biteship createLocation error: '.$e->getMessage());

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

            Log::warning('Biteship updateLocation failed: '.$response->body());

            return false;
        } catch (\Exception $e) {
            Log::error('Biteship updateLocation error: '.$e->getMessage());

            return false;
        }
    }

    public function createOrder(Order $order): ?array
    {
        try {
            if (empty($this->apiKey)) {
                return ['error' => 'API Key Biteship belum dikonfigurasi di file .env (BITESHIP_API_KEY).'];
            }

            if (! $order->address) {
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
                'address' => ($setting && $setting->address_line) ? $setting->address_line.', '.$setting->city.', '.$setting->province : 'Jl. Jembatan Besi II No. 1, Jakarta Barat',
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
                'address' => $order->address->address_line.', '.$order->address->city.', '.$order->address->province,
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

            // Outside a same-day courier's hours this used to ask Biteship for a
            // 'scheduled' delivery at 09:00 the next morning. Biteship refuses —
            // "Courier is not available for scheduled delivery" — because these
            // couriers are on demand: they collect now or not at all. The order
            // then sat paid and unbooked, because the three retries were spent
            // within minutes and the admin's re-book button recomputed the same
            // doomed request.
            //
            // The booking is always "now"; waiting for the window is
            // BookBiteshipOrder's job, via CourierSchedule.
            if (! CourierSchedule::isOpenNow($courierCompany, $courierType)) {
                $opensAt = CourierSchedule::nextOpening($courierCompany, $courierType);

                return ['error' => 'Kurir '.strtoupper($courierCompany).' belum bisa menjemput di luar jam operasional. Penjemputan berikutnya mulai '.$opensAt?->translatedFormat('d M H:i').' WIB.'];
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
                'delivery_type' => 'now',
                'order_note' => "Order #{$order->order_number}",
                'items' => $items,
            ];

            Log::info('Biteship Create Order Payload: '.json_encode($payload));

            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/orders", $payload);

            if ($response->successful()) {
                $data = $response->json();

                // Store the Biteship Order ID and Tracking ID for future reliable webhooks and links
                if (isset($data['id'])) {
                    $order->update([
                        'biteship_order_id' => $data['id'],
                        'courier_tracking_id' => $data['courier']['tracking_id'] ?? $data['courier_tracking_id'] ?? null,
                    ]);
                }

                // Ensure success field is set
                if (! isset($data['success'])) {
                    $data['success'] = true;
                }

                return $data;
            }

            Log::warning('Biteship createOrder failed: '.$response->body());

            $errorMessage = $response->json('error')
                ?? $response->json('message')
                ?? ($response->json('errors') ? implode(', ', (array) $response->json('errors')) : null)
                ?? 'Unknown error from Biteship (HTTP Status '.$response->status().')';

            return ['error' => $errorMessage];
        } catch (\Exception $e) {
            Log::error('Biteship createOrder error: '.$e->getMessage());

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

            Log::warning('Biteship getOrder failed: '.$response->body());

            return null;
        } catch (\Exception $e) {
            Log::error('Biteship getOrder error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Cancel an order at Biteship (POST /v1/orders/:id/cancel).
     *
     * This is one of the few real order actions the merchant API exposes — the
     * granular courier statuses (allocated/picking_up/…) are driven by Biteship
     * and only ever reach us through the webhook, they cannot be set from here.
     *
     * @return array{success?: bool, error?: string}
     */
    public function cancelOrder(Order $order, string $reason = 'Dibatalkan oleh penjual'): array
    {
        try {
            if (empty($this->apiKey)) {
                return ['error' => 'API Key Biteship belum dikonfigurasi di file .env (BITESHIP_API_KEY).'];
            }

            if (empty($order->biteship_order_id)) {
                return ['error' => 'Pesanan ini belum terhubung ke Biteship, jadi tidak ada pengiriman untuk dibatalkan.'];
            }

            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/orders/{$order->biteship_order_id}/cancel", [
                    'cancellation_reason' => $reason,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (! isset($data['success'])) {
                    $data['success'] = true;
                }

                return $data;
            }

            Log::warning('Biteship cancelOrder failed: '.$response->body());

            $errorMessage = $response->json('error')
                ?? $response->json('message')
                ?? 'Gagal membatalkan pesanan di Biteship (HTTP Status '.$response->status().')';

            return ['error' => $errorMessage];
        } catch (\Exception $e) {
            Log::error('Biteship cancelOrder error: '.$e->getMessage());

            return ['error' => $e->getMessage()];
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

            Log::warning('Biteship deleteLocation failed: '.$response->body());

            return false;
        } catch (\Exception $e) {
            Log::error('Biteship deleteLocation error: '.$e->getMessage());

            return false;
        }
    }
}

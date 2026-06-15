<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'order_number', 'biteship_order_id', 'courier_tracking_id', 'address_id', 'coupon_id', 'discount_amount',
        'subtotal', 'shipping_cost', 'total', 'status', 'payment_status', 'payment_method', 'pakasir_link',
        'pakasir_order_id', 'shipping_courier', 'shipping_service',
        'tracking_number', 'notes', 'paid_at',
    ];

    protected $appends = ['status_label', 'status_color', 'tracking_url'];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::updated(function ($order) {
            // Check if status transitioned to 'paid'
            if ($order->wasChanged('status') && $order->status === 'paid') {
                // Prevent real API calls during tests unless the service is explicitly mocked/bound in the container
                if (app()->runningUnitTests() && !app()->bound(\App\Services\BiteshipService::class)) {
                    return;
                }

                if (empty($order->biteship_order_id)) {
                    try {
                        $biteship = app(\App\Services\BiteshipService::class);
                        $result = $biteship->createOrder($order);
                        
                        if ($result && isset($result['success']) && $result['success']) {
                            static::withoutEvents(function () use ($order, $result) {
                                $order->update([
                                    'status' => 'processing',
                                    'biteship_order_id' => $result['id'] ?? $order->biteship_order_id,
                                    'courier_tracking_id' => $result['courier_tracking_id'] ?? $order->courier_tracking_id,
                                    'tracking_number' => $result['courier']['waybill_id'] ?? $order->tracking_number,
                                ]);
                            });
                            \Illuminate\Support\Facades\Log::info("Biteship Auto-Process: Order #{$order->order_number} successfully processed to Biteship.");
                        } else {
                            $err = $result['error'] ?? 'Unknown error';
                            \Illuminate\Support\Facades\Log::warning("Biteship Auto-Process Failed for Order #{$order->order_number}: " . $err);
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Biteship Auto-Process Error for Order #{$order->order_number}: " . $e->getMessage());
                    }
                }
            }
        });
    }

    public function getTrackingUrlAttribute(): string
    {
        // Use Courier Tracking ID (ttce...) if available, otherwise fallback to Tracking Number (WYB...)
        $id = $this->courier_tracking_id ?? $this->tracking_number;
        if (!$id) return '';

        $baseUrl = 'https://track.biteship.com/' . $id;

        // Smart Sandbox Detection: If using a biteship_test key, append the development environment flag
        $apiKey = config('biteship.api_key');
        $isSandbox = str_starts_with($apiKey ?? '', 'biteship_test.');
        
        // Only apply flag to Biteship IDs (ttce or WYB)
        $isBiteshipId = str_starts_with($id, 'ttce') || str_starts_with($id, 'WYB');

        if ($isSandbox && $isBiteshipId) {
            return $baseUrl . '?environment=development';
        }

        return $baseUrl;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public static function generateOrderNumber(): string
    {
        return 'GGR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->total, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'awaiting_payment' => 'Menunggu Pembayaran',
            'paid' => 'Dibayar',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'awaiting_payment' => 'orange',
            'paid' => 'emerald',
            'processing' => 'blue',
            'shipped' => 'indigo',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}

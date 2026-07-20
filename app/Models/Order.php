<?php

namespace App\Models;

use App\Jobs\BookBiteshipOrder;
use App\Services\BiteshipService;
use App\Support\StorefrontCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        // Invalidate dashboard metrics & best-seller caches on any order change.
        $flushCache = fn () => StorefrontCache::forget(StorefrontCache::ORDER_KEYS);
        static::saved($flushCache);
        static::deleted($flushCache);

        static::updated(function ($order) {
            // When an order becomes paid and is not yet booked, hand the Biteship
            // booking to a queued job. This deliberately does NOT call the API
            // here: the paid transition happens inside PakasirService::
            // markOrderPaid()'s DB transaction + row lock, so a synchronous HTTP
            // call would hold the lock for the whole round-trip. The job runs on
            // a worker once the transaction has committed. See BookBiteshipOrder.
            if ($order->wasChanged('status') && $order->status === 'paid' && empty($order->biteship_order_id)) {
                // Prevent real API calls during tests unless the service is explicitly mocked/bound in the container.
                if (app()->runningUnitTests() && ! app()->bound(BiteshipService::class)) {
                    return;
                }

                BookBiteshipOrder::dispatch($order->id);
            }
        });
    }

    public function getTrackingUrlAttribute(): string
    {
        // Prioritize Courier Tracking ID (e.g. 8wzOjhwBw8pbfdl0y8QrObNZ) over courier Waybill ID (WYB...)
        $id = $this->courier_tracking_id ?? $this->tracking_number;
        if (! $id) {
            return '';
        }

        $baseUrl = 'https://track.biteship.com/'.$id;

        // Smart Sandbox Detection: If using a biteship_test key, append the development environment flag
        $apiKey = config('biteship.api_key');
        $isSandbox = str_starts_with($apiKey ?? '', 'biteship_test.');

        // Apply flag to Biteship IDs (ttce, WYB, or 24-character alphanumeric tracking ID)
        $isBiteshipId = str_starts_with($id, 'ttce') || str_starts_with($id, 'WYB') || preg_match('/^[a-zA-Z0-9]{24}$/', $id);

        if ($isSandbox && $isBiteshipId) {
            return $baseUrl.'?environment=development';
        }

        return $baseUrl;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class)->withTrashed();
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
        return 'GGR-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -6));
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp '.number_format((float) $this->total, 0, ',', '.');
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

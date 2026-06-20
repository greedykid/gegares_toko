<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id', 'label', 'recipient_name', 'phone', 'address_line',
        'area_id', 'city', 'province', 'postal_code',
        'latitude', 'longitude', 'is_primary', 'biteship_location_id',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute(): string
    {
        return "{$this->address_line}, {$this->city}, {$this->province} {$this->postal_code}";
    }
}

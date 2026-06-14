<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'id',
        'store_name',
        'contact_phone',
        'contact_email',
        'address_line',
        'area_id',
        'city',
        'province',
        'postal_code',
        'latitude',
        'longitude',
        'biteship_location_id',
    ];
}

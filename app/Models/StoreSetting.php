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
        
        // Hero Content
        'hero_badge',
        'hero_title',
        'hero_subtitle',

        // FAQ & CTA
        'faq_items',
        'cta_title',
        'cta_subtitle',

        // About Content
        'about_title',
        'about_subtitle',
        'about_story_title',
        'about_story_content',
        'about_vision',
        'about_mission',
        'about_gallery',

        // Contact Content
        'contact_whatsapp',
        'contact_hours',
    ];

    protected $casts = [
        'faq_items' => 'array',
        'about_mission' => 'array',
        'about_gallery' => 'array',
    ];
}

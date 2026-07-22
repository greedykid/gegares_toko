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
        'about_gallery_badge',
        'about_gallery_title',
        'about_gallery_subtitle',

        // Contact Content
        'contact_whatsapp',
        'contact_hours',
        // contact_hours is the free text shown on the contact page; these two
        // are what the shipping logic reads. See App\Support\StoreSchedule.
        'opens_at',
        'closes_at',

        // Footer
        'payment_logos',
    ];

    protected $casts = [
        'faq_items' => 'array',
        'about_mission' => 'array',
        'about_gallery' => 'array',
        'payment_logos' => 'array',
    ];

    protected static function booted(): void
    {
        // StoreSchedule memoises the trading hours for the request, so changing
        // them here has to drop that too — otherwise the admin saves new hours
        // and the same request keeps deciding pickups with the old ones.
        $flush = function () {
            \Illuminate\Support\Facades\Cache::forget('store_settings');
            \App\Support\StoreSchedule::forgetCachedHours();
        };

        static::saved($flush);
        static::deleted($flush);
    }
}

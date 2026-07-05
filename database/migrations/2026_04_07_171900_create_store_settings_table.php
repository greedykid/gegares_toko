<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name')->default('Gegares Ecommerce');
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('address_line')->nullable();
            $table->string('area_id')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('biteship_location_id')->nullable();

            // Hero
            $table->string('hero_badge')->nullable();
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();

            // FAQ & CTA
            $table->json('faq_items')->nullable();
            $table->string('cta_title')->nullable();
            $table->text('cta_subtitle')->nullable();

            // About
            $table->string('about_title')->nullable();
            $table->text('about_subtitle')->nullable();
            $table->string('about_story_title')->nullable();
            $table->text('about_story_content')->nullable();
            $table->text('about_vision')->nullable();
            $table->json('about_mission')->nullable();
            $table->json('about_gallery')->nullable();
            $table->string('about_gallery_badge')->nullable();
            $table->string('about_gallery_title')->nullable();
            $table->text('about_gallery_subtitle')->nullable();

            // Contact
            $table->string('contact_whatsapp')->nullable();
            $table->text('contact_hours')->nullable();

            // Footer payment method logos (array of image paths)
            $table->json('payment_logos')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};

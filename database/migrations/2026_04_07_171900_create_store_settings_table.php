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
            $table->string('store_name', 100)->default('Gegares Ecommerce');
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_email')->nullable(); // 255: RFC-max email length
            $table->string('address_line', 255)->nullable();
            $table->string('area_id', 64)->nullable(); // Biteship area id
            $table->string('city', 60)->nullable();
            $table->string('province', 60)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('biteship_location_id', 40)->nullable();

            // Hero
            $table->string('hero_badge', 60)->nullable();
            $table->string('hero_title', 150)->nullable(); // headline
            $table->text('hero_subtitle')->nullable();

            // FAQ & CTA
            $table->json('faq_items')->nullable();
            $table->string('cta_title', 150)->nullable(); // headline
            $table->text('cta_subtitle')->nullable();

            // About
            $table->string('about_title', 150)->nullable(); // headline
            $table->text('about_subtitle')->nullable();
            $table->string('about_story_title', 150)->nullable(); // headline
            $table->text('about_story_content')->nullable();
            $table->text('about_vision')->nullable();
            $table->json('about_mission')->nullable();
            $table->json('about_gallery')->nullable();
            $table->string('about_gallery_badge', 60)->nullable();
            $table->string('about_gallery_title', 150)->nullable(); // headline
            $table->text('about_gallery_subtitle')->nullable();

            // Contact
            $table->string('contact_whatsapp', 20)->nullable();
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

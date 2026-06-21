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
        Schema::table('store_settings', function (Blueprint $table) {
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

            // Contact
            $table->string('contact_whatsapp')->nullable();
            $table->text('contact_hours')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'hero_badge',
                'hero_title',
                'hero_subtitle',
                'faq_items',
                'cta_title',
                'cta_subtitle',
                'about_title',
                'about_subtitle',
                'about_story_title',
                'about_story_content',
                'about_vision',
                'about_mission',
                'about_gallery',
                'contact_whatsapp',
                'contact_hours',
            ]);
        });
    }
};

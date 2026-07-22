<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When the shop is actually staffed.
 *
 * `contact_hours` already exists but is free text ("Setiap Hari: 06:00 - 17:00
 * WIB\nPemesanan WhatsApp: 24 Jam") meant for the contact page — nothing can
 * reliably compute "are we open?" from it.
 *
 * This matters because a courier cannot collect from an empty shop. Biteship
 * happily accepts an instant booking at 02:39, and it did in production, but
 * nobody was there to hand over the food. Pickup has to wait for the shop as
 * well as the courier.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->time('opens_at')->default('06:00')->after('contact_hours');
            $table->time('closes_at')->default('17:00')->after('opens_at');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['opens_at', 'closes_at']);
        });
    }
};

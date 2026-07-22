<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Integration credentials the admin can edit from the panel, for a host where
 * editing .env is awkward (shared cPanel, no shell).
 *
 * Values are encrypted with APP_KEY before they are written, so a database dump
 * or a backup does not hand over the payment and mail credentials in plaintext.
 * .env stays the fallback: a row only overrides config when it holds a value.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            // Encrypted ciphertext — far longer than the value it holds.
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_credentials');
    }
};

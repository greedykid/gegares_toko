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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email')->unique(); // 255: RFC-max email length
            $table->string('google_id', 40)->nullable()->unique()->index(); // Google "sub" (~21 digits)
            $table->string('google_avatar', 512)->nullable(); // avatar URL
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password'); // 255: room for bcrypt/argon hashes
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->string('avatar', 255)->nullable(); // stored file path
            $table->string('phone', 20)->nullable(); // +62 8xx-xxxx-xxxx
            $table->json('notification_settings')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

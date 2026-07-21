<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-customer cap for a coupon.
 *
 * `usage_limit` only caps total redemptions, so one customer could spend a promo
 * on every order until the global quota ran out. NULL keeps the old behaviour
 * (unlimited per customer) so existing promos are not silently tightened.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->unsignedInteger('usage_limit_per_user')->nullable()->after('usage_limit');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('usage_limit_per_user');
        });
    }
};

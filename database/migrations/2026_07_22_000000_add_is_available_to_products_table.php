<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manual availability switch, on top of the numeric stock.
 *
 * Jajanan pasar is cooked daily, so the shop often knows an item is off the
 * menu before the counter hits zero. This lets an admin mark it "Habis" in one
 * click while the stock number keeps doing its job behind the scenes
 * (oversell protection, quantity caps, restock on cancel, low-stock reports).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_available')->default(true)->after('stock')->index();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_available']);
            $table->dropColumn('is_available');
        });
    }
};

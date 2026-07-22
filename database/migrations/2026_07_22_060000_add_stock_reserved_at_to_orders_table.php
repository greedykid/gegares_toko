<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Marks an order that is currently holding stock off the shelf.
 *
 * Stock now leaves when the order is written, and comes back when the order is
 * cancelled. Deciding "does this order hold stock?" from its status alone was
 * only true for orders created after that change: every order already in the
 * database was built under the old rule (stock left on payment), so cancelling
 * one would have handed back units that never left. The marker makes the answer
 * explicit rather than inferred, and clearing it on release makes the release
 * idempotent without relying on the status guard.
 *
 * Backfill: an order that was paid and has not been cancelled did take its
 * stock out under the old rule, so it genuinely still holds it. Anything else —
 * unpaid, or already cancelled and restocked — holds nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('stock_reserved_at')->nullable()->after('paid_at');
        });

        DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereIn('status', ['processing', 'shipped', 'completed'])
            ->update(['stock_reserved_at' => DB::raw('COALESCE(paid_at, created_at)')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('stock_reserved_at');
        });
    }
};

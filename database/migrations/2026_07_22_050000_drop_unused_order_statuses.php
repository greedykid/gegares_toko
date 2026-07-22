<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `awaiting_payment` and `paid` were declared in the original enum but nothing
 * ever wrote them: an order is 'pending' until the payment settles, and
 * PakasirService::markOrderPaid() moves it straight to 'processing'. The only
 * way to reach either was the admin's status dropdown, which meant an admin
 * could park an order in a state no other code path understood — the courier
 * booking, the customer's order filters and the dashboard counters all had to
 * carry special cases for statuses that never occurred naturally.
 *
 * Existing rows are folded onto the status that actually describes them.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')->where('status', 'awaiting_payment')->update(['status' => 'pending']);
        DB::table('orders')->where('status', 'paid')->update(['status' => 'processing']);

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending', 'processing', 'shipped', 'completed', 'cancelled',
            ])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending', 'awaiting_payment', 'paid',
                'processing', 'shipped', 'completed', 'cancelled',
            ])->default('pending')->change();
        });
    }
};

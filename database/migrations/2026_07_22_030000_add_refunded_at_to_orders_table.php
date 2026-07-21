<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Close the money side of a cancelled order.
 *
 * Cancelling an order that was already paid left it reading "cancelled" while
 * payment_status stayed "paid", with nothing recording that the customer is owed
 * their money back. A timestamp is enough: an order that is cancelled and paid
 * but not yet stamped here still owes a refund (see Order::needsRefund()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('refunded_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('refunded_at');
        });
    }
};

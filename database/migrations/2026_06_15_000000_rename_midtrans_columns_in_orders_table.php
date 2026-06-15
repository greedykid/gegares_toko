<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('snap_token', 'pakasir_link');
            $table->renameColumn('midtrans_order_id', 'pakasir_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('pakasir_link', 'snap_token');
            $table->renameColumn('pakasir_order_id', 'midtrans_order_id');
        });
    }
};

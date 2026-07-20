<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number', 30)->unique(); // e.g. GGR-20260721-ABC123
            $table->string('biteship_order_id', 40)->nullable()->index();
            $table->string('courier_tracking_id', 40)->nullable()->index();
            $table->foreignId('address_id')->constrained();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->enum('status', [
                'pending', 'awaiting_payment', 'paid',
                'processing', 'shipped', 'completed', 'cancelled'
            ])->default('pending');
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('payment_status', [
                'unpaid', 'pending', 'paid', 'failed', 'expired'
            ])->default('unpaid');
            $table->string('payment_method', 30)->nullable(); // qris / pakasir
            $table->string('pakasir_link', 512)->nullable(); // hosted payment URL (with encoded redirect)
            $table->string('pakasir_order_id', 30)->nullable();
            $table->string('shipping_courier', 30)->nullable(); // grab / jne
            $table->string('shipping_service', 40)->nullable(); // same_day / reg
            $table->string('tracking_number', 64)->nullable(); // courier waybill
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for admin filtering, sorting, and dashboard aggregation
            $table->index(['status', 'created_at']);
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

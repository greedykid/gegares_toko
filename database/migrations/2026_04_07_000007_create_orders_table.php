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
            $table->string('order_number')->unique();
            $table->string('biteship_order_id')->nullable()->index();
            $table->string('courier_tracking_id')->nullable()->index();
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
            $table->string('payment_method')->nullable();
            $table->string('pakasir_link')->nullable();
            $table->string('pakasir_order_id')->nullable();
            $table->string('shipping_courier')->nullable();
            $table->string('shipping_service')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

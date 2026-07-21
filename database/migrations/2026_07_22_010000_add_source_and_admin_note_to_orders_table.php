<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Separate system markers from the customer's own note.
 *
 * `notes` is typed by the customer at checkout, yet the app also wrote its own
 * markers into it — "Dipesan otomatis via AI Chatbot" decided chatbot behaviour
 * and "PERLU DICEK: stok tidak mencukupi" warned the admin. A customer could
 * type either string and forge both. They get their own columns now.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('source', 20)->default('web')->after('payment_method')->index();
            $table->text('admin_note')->nullable()->after('notes');
        });

        // Existing chatbot orders keep behaving as such once detection moves off
        // the notes text.
        DB::table('orders')
            ->where('notes', 'LIKE', '%Dipesan otomatis via AI Chatbot%')
            ->update(['source' => 'chatbot']);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropColumn(['source', 'admin_note']);
        });
    }
};

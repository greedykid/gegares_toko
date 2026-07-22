<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One review per (order, product, customer). Duplicate prevention used to live
 * only in the Livewire component, so two concurrent submits (a double-click, or
 * two tabs) could both slip through and inflate the product's rating_count.
 *
 * The index excludes deleted_at on purpose: a soft-deleted row still occupies
 * the slot, so a customer whose review was removed by a moderator cannot simply
 * re-post the same one — the component surfaces that as "already reviewed".
 */
return new class extends Migration
{
    public function up(): void
    {
        // Safety net: collapse any pre-existing duplicates (keeping the earliest)
        // so the unique index can be added. Written DB-agnostically for sqlite too.
        DB::table('reviews')
            ->select('order_id', 'product_id', 'user_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('order_id', 'product_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->each(function ($row) {
                DB::table('reviews')
                    ->where('order_id', $row->order_id)
                    ->where('product_id', $row->product_id)
                    ->where('user_id', $row->user_id)
                    ->where('id', '!=', $row->keep_id)
                    ->delete();
            });

        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['order_id', 'product_id', 'user_id'], 'reviews_order_product_user_unique');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique('reviews_order_product_user_unique');
        });
    }
};

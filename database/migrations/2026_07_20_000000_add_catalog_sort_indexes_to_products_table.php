<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The catalogue (ProductController::index) lets shoppers sort and filter by
 * price and rating. Without these indexes those queries fall back to a filesort
 * over the whole products table as the catalogue grows. `is_featured` was
 * already indexed in the original table migration; these cover the remaining
 * sort/filter columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('price');
            $table->index('rating_avg');
            $table->index('rating_count');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['price']);
            $table->dropIndex(['rating_avg']);
            $table->dropIndex(['rating_count']);
        });
    }
};

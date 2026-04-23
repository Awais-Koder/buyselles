<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('status', 'products_status_index');
            $table->index('slug', 'products_slug_index');
            $table->index('added_by', 'products_added_by_index');
            $table->index('request_status', 'products_request_status_index');
            $table->index('featured', 'products_featured_index');
            $table->index('created_at', 'products_created_at_index');
            $table->index('unit_price', 'products_unit_price_index');
            // Composite index for the common active() scope filter
            $table->index(['status', 'added_by', 'request_status'], 'products_active_scope_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_index');
            $table->dropIndex('products_slug_index');
            $table->dropIndex('products_added_by_index');
            $table->dropIndex('products_request_status_index');
            $table->dropIndex('products_featured_index');
            $table->dropIndex('products_created_at_index');
            $table->dropIndex('products_unit_price_index');
            $table->dropIndex('products_active_scope_index');
        });
    }
};

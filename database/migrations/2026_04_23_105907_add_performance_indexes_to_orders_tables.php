<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // slug, position, parent_id were added in a partial run; add the remaining two
        Schema::table('categories', function (Blueprint $table) {
            $table->index('home_status', 'categories_home_status_index');
            $table->index('priority', 'categories_priority_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_customer_id_index');
            $table->dropIndex('orders_seller_id_index');
            $table->dropIndex('orders_order_status_index');
            $table->dropIndex('orders_payment_status_index');
            $table->dropIndex('orders_payment_method_index');
            $table->dropIndex('orders_created_at_index');
            $table->dropIndex('orders_order_group_id_index');
            $table->dropIndex('orders_seller_is_index');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropIndex('order_details_order_id_index');
            $table->dropIndex('order_details_product_id_index');
            $table->dropIndex('order_details_seller_id_index');
            $table->dropIndex('order_details_delivery_status_index');
            $table->dropIndex('order_details_payment_status_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_slug_index');
            $table->dropIndex('categories_position_index');
            $table->dropIndex('categories_parent_id_index');
            $table->dropIndex('categories_home_status_index');
            $table->dropIndex('categories_priority_index');
        });
    }
};

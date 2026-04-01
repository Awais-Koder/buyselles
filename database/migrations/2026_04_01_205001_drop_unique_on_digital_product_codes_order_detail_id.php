<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the unique constraint on order_detail_id so that multiple digital codes
     * can be assigned to the same order detail when the customer buys qty > 1.
     * A non-unique index is added to maintain query performance.
     */
    public function up(): void
    {
        Schema::table('digital_product_codes', function (Blueprint $table) {
            $table->dropUnique('digital_product_codes_order_detail_id_unique');
            $table->index('order_detail_id', 'dpc_order_detail_id_idx');
        });
    }

    /**
     * Restore the unique constraint (requires all duplicates to be cleaned up first).
     */
    public function down(): void
    {
        Schema::table('digital_product_codes', function (Blueprint $table) {
            $table->dropIndex('dpc_order_detail_id_idx');
            $table->unique('order_detail_id', 'digital_product_codes_order_detail_id_unique');
        });
    }
};

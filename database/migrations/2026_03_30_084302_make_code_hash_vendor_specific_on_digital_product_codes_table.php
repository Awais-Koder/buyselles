<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Change code_hash from a global unique to a vendor-specific composite unique.
     *
     * The same digital code (PIN) may exist across different vendors.
     * A single vendor (or admin scope where seller_id IS NULL) cannot
     * have the same code twice.
     */
    public function up(): void
    {
        Schema::table('digital_product_codes', function (Blueprint $table) {
            // Drop the old global unique index on code_hash alone
            $table->dropUnique(['code_hash']);

            // Add composite unique: same code is allowed for different vendors
            $table->unique(['code_hash', 'seller_id'], 'digital_product_codes_hash_seller_unique');
        });
    }

    public function down(): void
    {
        Schema::table('digital_product_codes', function (Blueprint $table) {
            $table->dropUnique('digital_product_codes_hash_seller_unique');
            $table->unique('code_hash');
        });
    }
};

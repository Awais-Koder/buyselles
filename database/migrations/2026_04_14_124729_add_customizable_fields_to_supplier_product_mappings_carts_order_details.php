<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Supplier product mappings — customizable flag + amount range
        Schema::table('supplier_product_mappings', function (Blueprint $table) {
            $table->boolean('is_customizable')->default(false)->after('is_active')
                ->comment('When true, customer can enter a variable amount instead of fixed denomination');
            $table->decimal('min_amount', 10, 2)->nullable()->after('is_customizable')
                ->comment('Minimum amount customer can enter (from supplier catalog)');
            $table->decimal('max_amount', 10, 2)->nullable()->after('min_amount')
                ->comment('Maximum amount customer can enter (from supplier catalog)');
        });

        // 2. Carts — store the customer-chosen amount for customizable products
        Schema::table('carts', function (Blueprint $table) {
            $table->decimal('custom_amount', 10, 2)->nullable()->after('price')
                ->comment('Customer-entered amount for variable/customizable products');
        });

        // 3. Order details — persist the custom amount through to order
        Schema::table('order_details', function (Blueprint $table) {
            $table->decimal('custom_amount', 10, 2)->nullable()->after('price')
                ->comment('Customer-entered amount for variable/customizable products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_product_mappings', function (Blueprint $table) {
            $table->dropColumn(['is_customizable', 'min_amount', 'max_amount']);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('custom_amount');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('custom_amount');
        });
    }
};

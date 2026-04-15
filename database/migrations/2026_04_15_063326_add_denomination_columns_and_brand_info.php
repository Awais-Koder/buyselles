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
        // 1. Add actual columns to the (currently empty) supplier_product_denominations table
        if (! Schema::hasColumn('supplier_product_denominations', 'supplier_product_id')) {
            Schema::table('supplier_product_denominations', function (Blueprint $table) {
                if (! Schema::hasColumn('supplier_product_denominations', 'supplier_product_mapping_id')) {
                    $table->unsignedBigInteger('supplier_product_mapping_id')->after('id');
                    $table->foreign('supplier_product_mapping_id', 'spd_mapping_id_foreign')
                        ->references('id')->on('supplier_product_mappings')->cascadeOnDelete();
                }
                $table->string('supplier_product_id')->after('supplier_product_mapping_id');
                $table->string('name')->nullable()->after('supplier_product_id');
                $table->enum('type', ['fixed', 'variable'])->default('fixed')->after('name');
                $table->decimal('face_value', 10, 2)->nullable()->after('type');
                $table->decimal('min_face_value', 10, 2)->nullable()->after('face_value');
                $table->decimal('max_face_value', 10, 2)->nullable()->after('min_face_value');
                $table->string('face_value_currency', 10)->default('USD')->after('max_face_value');
                $table->decimal('cost_price', 10, 2)->nullable()->after('face_value_currency');
                $table->string('cost_currency', 10)->nullable()->after('cost_price');
                $table->integer('stock_available')->nullable()->after('cost_currency');
                $table->boolean('is_active')->default(true)->after('stock_available');
                $table->integer('sort_order')->default(0)->after('is_active');
            });
        }

        // Add indexes (separate call to handle partial state)
        try {
            Schema::table('supplier_product_denominations', function (Blueprint $table) {
                $table->index(['supplier_product_mapping_id', 'type'], 'spd_mapping_type_index');
                $table->index('supplier_product_id', 'spd_product_id_index');
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Indexes may already exist from partial migration run
        }

        // 2. Add brand info columns to supplier_product_mappings
        if (! Schema::hasColumn('supplier_product_mappings', 'supplier_brand_id')) {
            Schema::table('supplier_product_mappings', function (Blueprint $table) {
                $table->string('supplier_brand_id')->nullable()->after('supplier_product_id');
                $table->string('supplier_brand_name')->nullable()->after('supplier_brand_id');
            });
        }

        // 3. Add supplier_denomination_id to carts
        if (! Schema::hasColumn('carts', 'supplier_denomination_id')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->unsignedBigInteger('supplier_denomination_id')->nullable()->after('custom_amount');
            });
        }

        // 4. Add supplier_denomination_id to order_details
        if (! Schema::hasColumn('order_details', 'supplier_denomination_id')) {
            Schema::table('order_details', function (Blueprint $table) {
                $table->unsignedBigInteger('supplier_denomination_id')->nullable()->after('custom_amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('supplier_denomination_id');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('supplier_denomination_id');
        });

        Schema::table('supplier_product_mappings', function (Blueprint $table) {
            $table->dropColumn(['supplier_brand_id', 'supplier_brand_name']);
        });

        Schema::table('supplier_product_denominations', function (Blueprint $table) {
            $table->dropForeign('spd_mapping_id_foreign');
            $table->dropIndex('spd_mapping_type_index');
            $table->dropIndex('spd_product_id_index');
            $table->dropColumn([
                'supplier_product_mapping_id',
                'supplier_product_id',
                'name',
                'type',
                'face_value',
                'min_face_value',
                'max_face_value',
                'face_value_currency',
                'cost_price',
                'cost_currency',
                'stock_available',
                'is_active',
                'sort_order',
            ]);
        });
    }
};

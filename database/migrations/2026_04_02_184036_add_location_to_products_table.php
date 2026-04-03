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
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'location_country_id')) {
                $table->unsignedBigInteger('location_country_id')->nullable()->after('product_type');
            }
            if (! Schema::hasColumn('products', 'location_city_id')) {
                $table->unsignedBigInteger('location_city_id')->nullable()->after('location_country_id');
            }
            if (! Schema::hasColumn('products', 'location_area_id')) {
                $table->unsignedBigInteger('location_area_id')->nullable()->after('location_city_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Columns are managed by 2026_04_02_183659_add_location_columns_to_products_table
    }
};

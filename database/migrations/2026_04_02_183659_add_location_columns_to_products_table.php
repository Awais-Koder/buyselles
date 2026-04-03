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
            $table->unsignedBigInteger('location_country_id')->nullable()->after('shipping_cost');
            $table->unsignedBigInteger('location_city_id')->nullable()->after('location_country_id');
            $table->unsignedBigInteger('location_area_id')->nullable()->after('location_city_id');

            $table->foreign('location_country_id')->references('id')->on('location_countries')->nullOnDelete();
            $table->foreign('location_city_id')->references('id')->on('location_cities')->nullOnDelete();
            $table->foreign('location_area_id')->references('id')->on('location_areas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['location_country_id']);
            $table->dropForeign(['location_city_id']);
            $table->dropForeign(['location_area_id']);
            $table->dropColumn(['location_country_id', 'location_city_id', 'location_area_id']);
        });
    }
};

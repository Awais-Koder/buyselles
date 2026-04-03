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
        Schema::table('shops', function (Blueprint $table) {
            if (Schema::hasColumn('shops', 'location_country_id')) {
                $table->dropForeign(['location_country_id']);
                $table->dropColumn('location_country_id');
            }
            $table->string('store_country', 10)->nullable()->after('location_area_id')->comment('ISO country code from COUNTRIES constant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('store_country');
            $table->unsignedBigInteger('location_country_id')->nullable()->after('location_area_id');
            $table->foreign('location_country_id')->references('id')->on('location_countries')->onDelete('set null');
        });
    }
};

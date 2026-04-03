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
        // Scope location_countries to the vendor that created them.
        // Null = admin-managed global country.
        Schema::table('location_countries', function (Blueprint $table) {
            $table->unsignedBigInteger('seller_id')->nullable()->after('id')->index();
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('cascade');
        });

        // Allow vendors to set their shop's base country in other-setup.
        if (! Schema::hasColumn('shops', 'location_country_id')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->unsignedBigInteger('location_country_id')->nullable()->after('location_area_id');
                $table->foreign('location_country_id')->references('id')->on('location_countries')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('shops', 'location_country_id')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->dropForeign(['location_country_id']);
                $table->dropColumn('location_country_id');
            });
        }

        Schema::table('location_countries', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropColumn('seller_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10)->nullable()->comment('ISO country code');
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('location_cities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id')->index();
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('country_id')->references('id')->on('location_countries')->onDelete('cascade');
        });

        Schema::create('location_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('city_id')->index();
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('cod_available')->default(true)->comment('Whether COD is allowed in this area');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('location_cities')->onDelete('cascade');
        });

        // Pivot: which areas a seller serves
        Schema::create('seller_service_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->index();
            $table->unsignedBigInteger('area_id')->index();
            $table->timestamps();

            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('cascade');
            $table->foreign('area_id')->references('id')->on('location_areas')->onDelete('cascade');
            $table->unique(['seller_id', 'area_id']);
        });

        // Per-vendor, per-area shipping rates
        Schema::create('vendor_shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->index();
            $table->unsignedBigInteger('area_id')->index();
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->integer('estimated_days')->nullable()->comment('Estimated delivery days');
            $table->timestamps();

            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('cascade');
            $table->foreign('area_id')->references('id')->on('location_areas')->onDelete('cascade');
            $table->unique(['seller_id', 'area_id']);
        });

        // Add location reference to shops table
        if (! Schema::hasColumn('shops', 'location_area_id')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->unsignedBigInteger('location_area_id')->nullable()->after('address');
                $table->foreign('location_area_id')->references('id')->on('location_areas')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('shops', 'location_area_id')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->dropForeign(['location_area_id']);
                $table->dropColumn('location_area_id');
            });
        }

        Schema::dropIfExists('vendor_shipping_rates');
        Schema::dropIfExists('seller_service_areas');
        Schema::dropIfExists('location_areas');
        Schema::dropIfExists('location_cities');
        Schema::dropIfExists('location_countries');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Remove seller_id from location_countries (make countries global/admin-only)
        if (Schema::hasColumn('location_countries', 'seller_id')) {
            Schema::table('location_countries', function (Blueprint $table) {
                if ($this->hasForeignKey('location_countries', 'location_countries_seller_id_foreign')) {
                    $table->dropForeign(['seller_id']);
                }
                $table->dropColumn('seller_id');
            });
        }

        // 2. Add store_country_id to shops (FK to location_countries)
        if (! Schema::hasColumn('shops', 'store_country_id')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->unsignedBigInteger('store_country_id')->nullable()->after('store_country');
                $table->unsignedBigInteger('store_city_id')->nullable()->after('store_country_id');

                $table->foreign('store_country_id')->references('id')->on('location_countries')->nullOnDelete();
                $table->foreign('store_city_id')->references('id')->on('location_cities')->nullOnDelete();
            });
        }

        // 3. Create city_requests table
        if (! Schema::hasTable('city_requests')) {
            Schema::create('city_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('seller_id');
                $table->unsignedBigInteger('country_id');
                $table->string('city_name', 191);
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('admin_note')->nullable();
                $table->unsignedBigInteger('approved_city_id')->nullable();
                $table->timestamps();

                $table->foreign('seller_id')->references('id')->on('sellers')->cascadeOnDelete();
                $table->foreign('country_id')->references('id')->on('location_countries')->cascadeOnDelete();
                $table->foreign('approved_city_id')->references('id')->on('location_cities')->nullOnDelete();
            });
        }

        // 4. Drop legacy tables
        Schema::dropIfExists('vendor_shipping_rates');
        Schema::dropIfExists('seller_service_areas');

        // 5. Drop legacy shops.location_area_id column
        if (Schema::hasColumn('shops', 'location_area_id')) {
            Schema::table('shops', function (Blueprint $table) {
                if ($this->hasForeignKey('shops', 'shops_location_area_id_foreign')) {
                    $table->dropForeign(['location_area_id']);
                }
                $table->dropColumn('location_area_id');
            });
        }
    }

    public function down(): void
    {
        // Restore shops.location_area_id
        Schema::table('shops', function (Blueprint $table) {
            $table->unsignedBigInteger('location_area_id')->nullable();
        });

        // Recreate seller_service_areas
        Schema::create('seller_service_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('area_id');
            $table->timestamps();
            $table->foreign('seller_id')->references('id')->on('sellers')->cascadeOnDelete();
            $table->foreign('area_id')->references('id')->on('location_areas')->cascadeOnDelete();
        });

        // Recreate vendor_shipping_rates
        Schema::create('vendor_shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('area_id');
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->string('estimated_days')->nullable();
            $table->timestamps();
            $table->foreign('seller_id')->references('id')->on('sellers')->cascadeOnDelete();
            $table->foreign('area_id')->references('id')->on('location_areas')->cascadeOnDelete();
        });

        // Drop city_requests
        Schema::dropIfExists('city_requests');

        // Drop new shop columns
        Schema::table('shops', function (Blueprint $table) {
            $table->dropForeign(['store_country_id']);
            $table->dropForeign(['store_city_id']);
            $table->dropColumn(['store_country_id', 'store_city_id']);
        });

        // Restore seller_id on location_countries
        Schema::table('location_countries', function (Blueprint $table) {
            $table->unsignedBigInteger('seller_id')->nullable()->after('id');
            $table->foreign('seller_id')->references('id')->on('sellers')->nullOnDelete();
        });
    }

    private function hasForeignKey(string $table, string $foreignKey): bool
    {
        $database = config('database.connections.mysql.database');
        $result = DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$database, $table, $foreignKey]
        );

        return count($result) > 0;
    }
};

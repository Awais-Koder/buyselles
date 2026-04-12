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
        Schema::table('digital_product_codes', function (Blueprint $table) {
            // 'manual' = uploaded by vendor/admin; 'supplier_api' = fetched from supplier
            $table->enum('source', ['manual', 'supplier_api'])->default('manual')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('digital_product_codes', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};

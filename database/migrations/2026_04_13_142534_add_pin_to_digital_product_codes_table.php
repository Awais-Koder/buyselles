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
            $table->text('pin')->nullable()->after('code')->comment('AES-256-CBC encrypted PIN');
        });
    }

    public function down(): void
    {
        Schema::table('digital_product_codes', function (Blueprint $table) {
            $table->dropColumn('pin');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemTaxSetupsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_tax_setups', function (Blueprint $table) {
            $table->id();
            $table->string('tax_type', 100)->default('order_wise');
            $table->string('country_code', 20)->nullable()->index();
            $table->string('tax_payer', 20)->nullable()->default('vendor');
            $table->tinyText('tax_ids', 255)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_included')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_tax_setups');
    }
}

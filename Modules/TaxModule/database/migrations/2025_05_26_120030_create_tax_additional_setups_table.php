<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxAdditionalSetupsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tax_additional_setups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->foreignId('system_tax_setup_id')->nullable();
            $table->tinyText('tax_ids', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_additional_setups');
    }
}

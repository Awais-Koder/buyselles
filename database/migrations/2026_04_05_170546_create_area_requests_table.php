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
        Schema::create('area_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->unsignedBigInteger('city_id');
            $table->string('area_name', 191);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->unsignedBigInteger('approved_area_id')->nullable();
            $table->timestamps();

            $table->foreign('seller_id')->references('id')->on('sellers')->nullOnDelete();
            $table->foreign('city_id')->references('id')->on('location_cities')->cascadeOnDelete();
            $table->foreign('approved_area_id')->references('id')->on('location_areas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_requests');
    }
};

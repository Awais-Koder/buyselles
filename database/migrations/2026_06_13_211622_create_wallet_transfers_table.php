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
        Schema::create('wallet_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('from_user_type')->comment('admin, vendor');
            $table->unsignedBigInteger('from_user_id');
            $table->string('to_user_type')->comment('vendor, customer');
            $table->unsignedBigInteger('to_user_id');
            $table->decimal('amount', 24, 3)->default(0);
            $table->string('reference', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transfers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_order_idempotency', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_api_key_id');
            $table->foreign('reseller_api_key_id')->references('id')->on('reseller_api_keys')->cascadeOnDelete();
            // The client-supplied idempotency key (per key, must be unique)
            $table->string('idempotency_key', 128);
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            // Cached response for idempotent replay
            $table->json('response_payload');
            $table->timestamp('created_at')->useCurrent();

            // Uniqueness: one idempotency key per API key
            $table->unique(['reseller_api_key_id', 'idempotency_key'], 'poi_key_unique');
            $table->index('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_order_idempotency');
    }
};

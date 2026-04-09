<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_api_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_api_key_id');
            $table->foreign('reseller_api_key_id')->references('id')->on('reseller_api_keys')->cascadeOnDelete();
            $table->string('method', 10);         // GET, POST, etc.
            $table->string('endpoint', 255);      // /api/v1/reseller/products
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            // Summary of request params — NEVER contains digital codes
            $table->json('request_summary')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['reseller_api_key_id', 'created_at']);
            $table->index('http_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_api_logs');
    }
};

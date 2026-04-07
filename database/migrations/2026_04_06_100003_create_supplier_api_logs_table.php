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
        Schema::create('supplier_api_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_api_id');
            $table->string('action', 50)->comment('fetch_products, place_order, fetch_stock, webhook, health_check');
            $table->string('endpoint', 500)->nullable();
            $table->string('method', 10)->default('GET');
            $table->json('request_payload')->nullable()->comment('Sanitized — no secrets');
            $table->json('response_payload')->nullable()->comment('Truncated if > 10KB');
            $table->unsignedSmallInteger('http_status_code')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->enum('status', ['success', 'failed', 'timeout', 'rate_limited'])->default('success');
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('supplier_api_id')->references('id')->on('supplier_apis')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->index(['supplier_api_id', 'created_at']);
            $table->index('order_id');
            $table->index(['action', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_api_logs');
    }
};

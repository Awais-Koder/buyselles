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
        Schema::create('supplier_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_api_id');
            $table->unsignedBigInteger('supplier_product_mapping_id');
            $table->unsignedBigInteger('order_id')->nullable()->comment('Platform order that triggered this');
            $table->unsignedBigInteger('order_detail_id')->nullable();
            $table->string('supplier_order_id', 255)->nullable()->comment('Supplier remote order reference');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('cost_per_unit', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->string('cost_currency', 3)->default('USD');
            $table->enum('status', ['pending', 'processing', 'fulfilled', 'partial', 'failed', 'refunded'])->default('pending');
            $table->text('codes_received')->nullable()->comment('Encrypted JSON array of received codes');
            $table->timestamp('fulfilled_at')->nullable();
            $table->text('failed_reason')->nullable();
            $table->unsignedInteger('attempt_count')->default(1);
            $table->timestamps();

            $table->foreign('supplier_api_id')->references('id')->on('supplier_apis')->cascadeOnDelete();
            $table->foreign('supplier_product_mapping_id')->references('id')->on('supplier_product_mappings')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->foreign('order_detail_id')->references('id')->on('order_details')->nullOnDelete();
            $table->index(['supplier_api_id', 'status']);
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_orders');
    }
};

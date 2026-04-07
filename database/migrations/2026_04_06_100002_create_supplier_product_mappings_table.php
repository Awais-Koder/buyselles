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
        Schema::create('supplier_product_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('supplier_api_id');
            $table->string('supplier_product_id', 255)->comment('Supplier catalog SKU/ID');
            $table->string('supplier_product_name', 255)->nullable()->comment('Cached name from supplier');
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->string('cost_currency', 3)->default('USD');
            $table->enum('markup_type', ['percent', 'flat'])->default('percent');
            $table->decimal('markup_value', 10, 2)->default(0);
            $table->unsignedInteger('priority')->default(0)->comment('Lower = try first in fallback chain');
            $table->boolean('auto_restock')->default(true);
            $table->unsignedInteger('min_stock_threshold')->default(5);
            $table->unsignedInteger('max_restock_qty')->default(50);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('supplier_api_id')->references('id')->on('supplier_apis')->cascadeOnDelete();
            $table->unique(['product_id', 'supplier_api_id']);
            $table->index(['is_active', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_product_mappings');
    }
};

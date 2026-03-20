<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_product_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->text('code'); // AES-256-CBC encrypted
            $table->enum('status', ['available', 'reserved', 'sold', 'failed'])->default('available')->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('order_detail_id')->nullable()->unique()->comment('null until this code is delivered');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        // Migrate any existing single codes from products.digital_product_code → pool
        if (Schema::hasColumn('products', 'digital_product_code')) {
            $products = DB::table('products')
                ->whereNotNull('digital_product_code')
                ->where('digital_product_code', '!=', '')
                ->where('product_type', 'digital')
                ->select(['id', 'digital_product_code'])
                ->get();

            foreach ($products as $product) {
                DB::table('digital_product_codes')->insert([
                    'product_id' => $product->id,
                    'code' => $product->digital_product_code, // already encrypted
                    'status' => 'available',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Drop the old single-code column
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('digital_product_code');
            });
        }

        // Sync current_stock on products to reflect pool count
        DB::statement("
            UPDATE products p
            SET p.current_stock = (
                SELECT COUNT(*) FROM digital_product_codes dpc
                WHERE dpc.product_id = p.id AND dpc.status = 'available'
            )
            WHERE p.product_type = 'digital'
        ");
    }

    public function down(): void
    {
        // Re-add the column before dropping the table so data can theoretically be restored
        if (! Schema::hasColumn('products', 'digital_product_code')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('digital_product_code')->nullable()->after('digital_product_type');
            });
        }

        Schema::dropIfExists('digital_product_codes');
    }
};

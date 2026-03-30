<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_product_codes', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('status')->index();
            $table->unsignedBigInteger('seller_id')->nullable()->after('product_id')->index();
        });

        // Back-fill seller_id from the parent product
        DB::statement('
            UPDATE digital_product_codes dpc
            INNER JOIN products p ON p.id = dpc.product_id
            SET dpc.seller_id = CASE
                WHEN p.added_by = \'seller\' THEN p.user_id
                ELSE NULL
            END
        ');
    }

    public function down(): void
    {
        Schema::table('digital_product_codes', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'seller_id']);
        });
    }
};

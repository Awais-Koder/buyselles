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
        Schema::table('orders', function (Blueprint $table) {
            // commission_type for vendor: percent | flat (default percent = legacy behaviour)
            $table->string('commission_type', 10)->default('percent')->after('admin_commission');
            // customer-facing service fee charged at checkout
            $table->decimal('customer_service_fee', 24, 2)->default(0)->after('commission_type');
            $table->string('customer_service_fee_type', 10)->default('percent')->after('customer_service_fee');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['commission_type', 'customer_service_fee', 'customer_service_fee_type']);
        });
    }
};

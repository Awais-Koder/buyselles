<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_wallets', function (Blueprint $table) {
            // Funds from partner API orders held in escrow for 48h before releasing to total_earning
            $table->decimal('pending_balance', 24, 2)->default(0)->after('total_earning');
        });
    }

    public function down(): void
    {
        Schema::table('seller_wallets', function (Blueprint $table) {
            $table->dropColumn('pending_balance');
        });
    }
};

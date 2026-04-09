<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reseller_api_keys', function (Blueprint $table) {
            // Replace boolean is_active with a 3-state status enum
            $table->enum('status', ['pending', 'active', 'inactive'])
                ->default('pending')
                ->after('is_active');

            // Vendor-originated requests: link to sellers table
            $table->unsignedBigInteger('seller_id')->nullable()->after('user_id');
            $table->foreign('seller_id')->references('id')->on('sellers')->nullOnDelete();

            // Admin approval audit trail
            $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            $table->foreign('approved_by')->references('id')->on('admins')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('admin_note')->nullable()->after('approved_at');

            // Request note from vendor when applying for a key
            $table->text('request_note')->nullable()->after('admin_note');

            $table->index('status');
            $table->index('seller_id');
        });
    }

    public function down(): void
    {
        Schema::table('reseller_api_keys', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['status']);
            $table->dropIndex(['seller_id']);
            $table->dropColumn(['status', 'seller_id', 'approved_by', 'approved_at', 'admin_note', 'request_note']);
        });
    }
};

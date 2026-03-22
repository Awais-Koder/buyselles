<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_product_codes', function (Blueprint $table): void {
            $table->string('serial_number', 191)->nullable()->after('code');
            $table->date('expiry_date')->nullable()->after('serial_number');
        });

        // Add 'expired' to the status ENUM (MySQL-safe approach)
        DB::statement("
            ALTER TABLE digital_product_codes
            MODIFY COLUMN status ENUM('available','reserved','sold','failed','expired')
            NOT NULL DEFAULT 'available'
        ");
    }

    public function down(): void
    {
        // Downgrade any 'expired' rows to 'failed' before shrinking the enum
        DB::statement("UPDATE digital_product_codes SET status = 'failed' WHERE status = 'expired'");

        DB::statement("
            ALTER TABLE digital_product_codes
            MODIFY COLUMN status ENUM('available','reserved','sold','failed')
            NOT NULL DEFAULT 'available'
        ");

        Schema::table('digital_product_codes', function (Blueprint $table): void {
            $table->dropColumn(['serial_number', 'expiry_date']);
        });
    }
};

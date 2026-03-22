<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_product_codes', function (Blueprint $table): void {
            // SHA-256 hex of the plain-text PIN (64 chars).
            // Stored so we can detect duplicate codes without decrypting AES ciphertext.
            // Unique globally — a gift-card PIN can only exist once across the entire pool.
            $table->string('code_hash', 64)->nullable()->after('code')->unique();
        });
    }

    public function down(): void
    {
        Schema::table('digital_product_codes', function (Blueprint $table): void {
            $table->dropUnique(['code_hash']);
            $table->dropColumn('code_hash');
        });
    }
};

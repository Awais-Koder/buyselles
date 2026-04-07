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
        Schema::create('supplier_apis', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('driver', 50)->comment('Driver key: generic_rest, reloadly, kinguin, etc.');
            $table->string('base_url', 500);
            $table->text('credentials')->comment('Encrypted JSON: {api_key, api_secret, client_id, ...}');
            $table->enum('auth_type', ['api_key', 'bearer_token', 'oauth2', 'basic', 'hmac'])->default('api_key');
            $table->json('settings')->nullable()->comment('Driver-specific config: sandbox, webhook_secret, custom_headers, etc.');
            $table->unsignedInteger('rate_limit_per_minute')->default(60);
            $table->unsignedInteger('priority')->default(0)->comment('Lower = higher priority in fallback chain');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sandbox')->default(false);
            $table->enum('health_status', ['healthy', 'degraded', 'down', 'unknown'])->default('unknown');
            $table->timestamp('health_checked_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'priority']);
            $table->index('health_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_apis');
    }
};

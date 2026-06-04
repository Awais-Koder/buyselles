<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rate_logs', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 10)->index();
            $table->decimal('old_rate', 20, 8);
            $table->decimal('new_rate', 20, 8);
            $table->string('source', 20);
            $table->json('api_response')->nullable();
            $table->string('status', 20);
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('exchange_rate_logs', function (Blueprint $table) {
            $table->index(['currency_code', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rate_logs');
    }
};

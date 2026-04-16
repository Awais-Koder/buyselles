<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispute_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_id')->constrained('disputes')->cascadeOnDelete();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->enum('changed_by_type', ['buyer', 'vendor', 'admin', 'system']);
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispute_status_logs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispute_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_id')->constrained('disputes')->cascadeOnDelete();
            $table->unsignedBigInteger('uploaded_by');
            $table->enum('user_type', ['buyer', 'vendor', 'admin']);
            $table->string('file_path');
            $table->enum('file_type', ['image', 'video', 'document']);
            $table->string('original_name');
            $table->unsignedInteger('file_size');
            $table->text('caption')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispute_evidence');
    }
};

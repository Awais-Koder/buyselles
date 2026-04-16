<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedBigInteger('order_detail_id')->nullable();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('sellers')->cascadeOnDelete();
            $table->enum('initiated_by', ['buyer', 'vendor']);
            $table->foreignId('reason_id')->nullable()->constrained('dispute_reasons')->nullOnDelete();
            $table->text('description');
            $table->enum('status', [
                'open',
                'vendor_response',
                'under_review',
                'resolved_refund',
                'resolved_release',
                'closed',
                'auto_closed',
            ])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->text('admin_decision')->nullable();
            $table->text('admin_note')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('vendor_deadline_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('buyer_id');
            $table->index('vendor_id');
            $table->index('status');
            $table->index('priority');

            $table->foreign('order_detail_id')->references('id')->on('order_details')->nullOnDelete();
            $table->foreign('resolved_by')->references('id')->on('admins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};

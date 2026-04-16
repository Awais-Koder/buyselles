<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('admin_commission', 15, 2);
            $table->decimal('seller_amount', 15, 2);
            $table->decimal('service_fee', 15, 2)->default(0);
            $table->enum('status', ['held', 'released', 'disputed', 'refunded'])->default('held');
            $table->string('payment_method');
            $table->timestamp('auto_release_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->enum('released_by', ['auto', 'buyer', 'admin'])->nullable();
            $table->unsignedBigInteger('dispute_id')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('seller_id');
            $table->index('status');
            $table->index('auto_release_at');

            $table->foreign('dispute_id')->references('id')->on('disputes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrows');
    }
};

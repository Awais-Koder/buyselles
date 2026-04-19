<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('escrows', function (Blueprint $table) {
            $table->string('released_by', 30)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('escrows', function (Blueprint $table) {
            $table->enum('released_by', ['auto', 'buyer', 'admin'])->nullable()->change();
        });
    }
};

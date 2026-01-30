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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tax_number', 50)->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('address', 1000)->nullable();
            $table->tinyInteger('status')->default(1); // 0: pasif, 1: aktif
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

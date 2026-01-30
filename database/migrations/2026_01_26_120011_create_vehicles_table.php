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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate', 20)->unique();
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->integer('year')->nullable();
            $table->string('vehicle_type', 50); // truck, van, car, etc.
            $table->decimal('capacity_kg', 10, 2)->nullable();
            $table->decimal('capacity_m3', 10, 2)->nullable();
            $table->tinyInteger('status')->default(1); // 0: pasif, 1: aktif, 2: bakımda
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index('branch_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};

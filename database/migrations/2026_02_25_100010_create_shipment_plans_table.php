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
        Schema::create('shipment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('planned_pickup_date')->nullable();
            $table->timestamp('planned_delivery_date')->nullable();
            $table->string('status', 50)->default('planned'); // planned, loading, in_transit, delivered, cancelled
            $table->string('notes', 1000)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('order_id');
            $table->index('vehicle_id');
            $table->index('driver_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_plans');
    }
};


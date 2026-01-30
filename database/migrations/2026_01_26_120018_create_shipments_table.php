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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('no action');
            $table->foreignId('driver_id')->nullable()->constrained('employees')->onDelete('no action');
            $table->string('status', 50); // assigned, loaded, in_transit, delivered
            $table->timestamp('pickup_date')->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->string('qr_code', 100)->nullable();
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
        Schema::dropIfExists('shipments');
    }
};

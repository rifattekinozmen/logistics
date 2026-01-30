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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('order_number', 50)->unique();
            $table->string('status', 50); // pending, assigned, in_transit, delivered, cancelled
            $table->string('pickup_address', 1000);
            $table->string('delivery_address', 1000);
            $table->timestamp('planned_pickup_date')->nullable();
            $table->timestamp('planned_delivery_date')->nullable();
            $table->timestamp('actual_pickup_date')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->decimal('total_weight', 10, 2)->nullable();
            $table->decimal('total_volume', 10, 2)->nullable();
            $table->boolean('is_dangerous')->default(false);
            $table->string('notes', 2000)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('no action');
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('status');
            $table->index('planned_delivery_date');
            $table->index('delivered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

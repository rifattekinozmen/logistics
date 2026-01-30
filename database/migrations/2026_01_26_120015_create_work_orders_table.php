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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_number', 50)->unique();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->string('work_order_type', 50); // maintenance, repair, inspection, emergency
            $table->string('priority', 50); // low, medium, high, urgent
            $table->string('description', 2000)->nullable();
            $table->integer('estimated_duration')->nullable(); // minutes
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->integer('actual_duration')->nullable(); // minutes
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->string('status', 50); // pending_approval, approved, in_progress, completed, cancelled
            $table->foreignId('service_provider_id')->nullable()->constrained('service_providers')->onDelete('no action');
            $table->foreignId('assigned_technician_id')->nullable()->constrained('employees')->onDelete('no action');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('no action');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('no action');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};

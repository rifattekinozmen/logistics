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
        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('shift_schedules')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('no action');
            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('shift_type', 50);
            $table->decimal('total_hours', 4, 2);
            $table->boolean('is_overtime')->default(false);
            $table->string('notes', 1000)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_assignments');
    }
};

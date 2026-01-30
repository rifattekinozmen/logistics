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
        Schema::create('personnel_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('attendance_date');
            $table->string('attendance_type', 50); // full_day, half_day, leave, annual_leave, report, overtime
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->string('leave_type', 50)->nullable();
            $table->string('report_type', 50)->nullable();
            $table->string('report_document', 1000)->nullable();
            $table->string('notes', 1000)->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('no action');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'attendance_date']);
            $table->index('attendance_date');
            $table->index('attendance_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_attendance');
    }
};

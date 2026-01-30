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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('payroll_number')->unique();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('base_salary', 10, 2);
            $table->decimal('overtime_amount', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('deduction', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('social_security', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2);
            $table->string('status')->default('draft'); // draft, finalized, paid
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'period_start', 'period_end']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};

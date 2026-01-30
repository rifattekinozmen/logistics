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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('no action');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('position_id')->nullable()->constrained('positions')->onDelete('no action');
            $table->string('employee_number', 50)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->date('hire_date');
            $table->tinyInteger('status')->default(1); // 0: pasif, 1: aktif, 2: izinli
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('branch_id');
            $table->index('position_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

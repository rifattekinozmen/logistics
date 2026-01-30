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
        Schema::create('shift_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->string('shift_type', 50); // morning, afternoon, night, custom
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('break_duration')->nullable(); // minutes
            $table->decimal('total_hours', 4, 2);
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('no action');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('no action');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_templates');
    }
};

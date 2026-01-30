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
        Schema::create('ai_reports', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50); // finance, operations, hr, fleet, document
            $table->text('summary_text');
            $table->string('severity', 20); // low, medium, high
            $table->text('data_snapshot')->nullable(); // JSON
            $table->timestamp('generated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_reports');
    }
};

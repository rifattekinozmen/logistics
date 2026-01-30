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
        Schema::create('vehicle_damages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->nullable()->constrained('vehicle_inspections')->onDelete('no action');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('no action');
            $table->date('damage_date');
            $table->string('damage_location', 50); // front, rear, right, left, top, bottom
            $table->string('damage_type', 50); // scratch, dent, crack, paint_damage
            $table->string('damage_size', 50)->nullable();
            $table->string('severity', 50); // minor, moderate, severe
            $table->string('description', 2000)->nullable();
            $table->text('digital_drawing_data')->nullable(); // JSON
            $table->string('status', 50); // detected, approved, repaired, cancelled
            $table->text('photos')->nullable(); // JSON array
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('no action');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('no action');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('repaired_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_damages');
    }
};

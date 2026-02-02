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
        Schema::create('delivery_report_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_import_batch_id')->constrained('delivery_import_batches')->onDelete('cascade');
            $table->unsignedInteger('row_index');
            $table->json('row_data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_report_rows');
    }
};

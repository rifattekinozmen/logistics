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
        Schema::create('delivery_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_path', 1000);
            $table->integer('total_rows');
            $table->integer('processed_rows')->default(0);
            $table->integer('successful_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->string('status', 50); // processing, completed, failed
            $table->foreignId('imported_by')->nullable()->constrained('users')->onDelete('no action');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_import_batches');
    }
};

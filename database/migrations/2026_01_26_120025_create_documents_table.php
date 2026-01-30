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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documentable_id'); // polymorphic
            $table->string('documentable_type', 100); // App\Models\Employee, App\Models\Vehicle, etc.
            $table->string('category', 100); // identity, license, insurance, invoice, etc.
            $table->string('name');
            $table->string('file_path', 1000);
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->integer('version')->default(1);
            $table->string('tags', 500)->nullable(); // JSON array
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('no action');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['documentable_id', 'documentable_type']);
            $table->index('category');
            $table->index('valid_until');
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('sku', 100)->unique();
            $table->string('barcode', 100)->nullable();
            $table->string('name');
            $table->string('category', 100)->nullable();
            $table->string('unit', 50); // piece, kg, liter, m2, m3
            $table->decimal('min_stock_level', 10, 2)->default(0);
            $table->decimal('max_stock_level', 10, 2)->nullable();
            $table->decimal('critical_stock_level', 10, 2)->nullable();
            $table->boolean('track_serial')->default(false);
            $table->boolean('track_lot')->default(false);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};

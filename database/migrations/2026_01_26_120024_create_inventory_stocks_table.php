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
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained('warehouse_locations')->onDelete('no action');
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('no action');
            $table->decimal('quantity', 10, 2);
            $table->string('serial_number', 100)->nullable();
            $table->string('lot_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            $table->index(['warehouse_id', 'item_id', 'location_id']);
            $table->index('serial_number');
            $table->index('lot_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_stocks');
    }
};

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
        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('warehouse_locations')->onDelete('no action');
            $table->string('location_type', 50); // zone, aisle, rack, shelf, position
            $table->string('code', 50);
            $table->string('name');
            $table->string('full_path', 500); // A-01-B-02-C-03
            $table->decimal('capacity', 10, 2)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_locations');
    }
};

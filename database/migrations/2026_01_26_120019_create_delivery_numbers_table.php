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
        Schema::create('delivery_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('delivery_number', 100)->unique();
            $table->string('customer_name');
            $table->string('customer_phone', 20)->nullable();
            $table->string('delivery_address', 1000);
            $table->foreignId('location_id')->nullable();
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('no action');
            $table->string('status', 50); // new, matched, order_created, shipment_assigned, completed, error
            $table->string('error_message', 1000)->nullable();
            $table->foreignId('import_batch_id')->nullable();
            $table->integer('row_number')->nullable();
            $table->string('notes', 2000)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_numbers');
    }
};

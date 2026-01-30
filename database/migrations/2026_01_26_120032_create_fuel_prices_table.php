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
        Schema::create('fuel_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->date('price_date');
            $table->string('price_type', 50); // purchase, station
            $table->decimal('price', 10, 4); // TL/Litre
            $table->string('supplier_name')->nullable();
            $table->string('region', 100)->nullable();
            $table->string('notes', 1000)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('no action');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_prices');
    }
};

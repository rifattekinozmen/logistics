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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('no action');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('no action');
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('no action');
            $table->foreignId('neighborhood_id')->nullable()->constrained('neighborhoods')->onDelete('no action');
            $table->string('address_line', 1000)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};

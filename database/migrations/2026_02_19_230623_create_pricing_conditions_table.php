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
        Schema::create('pricing_conditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('condition_type', 30)->comment('weight_based,distance_based,flat,zone_based');
            $table->string('name', 150);
            $table->string('route_origin', 100)->nullable();
            $table->string('route_destination', 100)->nullable();
            $table->decimal('weight_from', 10, 2)->nullable();
            $table->decimal('weight_to', 10, 2)->nullable();
            $table->decimal('distance_from', 10, 2)->nullable();
            $table->decimal('distance_to', 10, 2)->nullable();
            $table->decimal('price_per_kg', 10, 4)->nullable();
            $table->decimal('price_per_km', 10, 4)->nullable();
            $table->decimal('flat_rate', 10, 2)->nullable();
            $table->decimal('min_charge', 10, 2)->nullable()->default(0);
            $table->string('currency', 3)->default('TRY');
            $table->string('vehicle_type', 50)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('notes', 1000)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'condition_type', 'status']);
            $table->index(['route_origin', 'route_destination']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_conditions');
    }
};

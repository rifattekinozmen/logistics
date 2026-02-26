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
        Schema::table('vehicles', function (Blueprint $table): void {
            $table->string('license_number', 255)->nullable()->after('plate');
            $table->string('series', 100)->nullable()->after('brand');
            $table->string('color', 50)->nullable()->after('year');
            $table->unsignedBigInteger('mileage')->nullable()->after('color');
            $table->string('fuel_type', 20)->nullable()->after('vehicle_type');
            $table->string('transmission', 20)->nullable()->after('fuel_type');
            $table->string('owner_type', 20)->nullable()->after('transmission');
            $table->string('engine_number', 100)->nullable()->after('owner_type');
            $table->string('vin_number', 32)->nullable()->after('engine_number');
            $table->string('hgs_number', 100)->nullable()->after('vin_number');
            $table->string('hgs_bank', 50)->nullable()->after('hgs_number');
            $table->text('notes')->nullable()->after('hgs_bank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table): void {
            $table->dropColumn([
                'license_number',
                'series',
                'color',
                'mileage',
                'fuel_type',
                'transmission',
                'owner_type',
                'engine_number',
                'vin_number',
                'hgs_number',
                'hgs_bank',
                'notes',
            ]);
        });
    }
};


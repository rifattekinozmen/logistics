<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * (Q) Güç Ağırlık Oranı — sadece motosiklet için kullanılır; lojistik/ticari araç için gereksiz.
     */
    public function up(): void
    {
        if (Schema::hasColumn('vehicles', 'power_weight_ratio')) {
            Schema::table('vehicles', function (Blueprint $table): void {
                $table->dropColumn('power_weight_ratio');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table): void {
            $table->decimal('power_weight_ratio', 8, 2)->nullable()->after('engine_power_kw');
        });
    }
};

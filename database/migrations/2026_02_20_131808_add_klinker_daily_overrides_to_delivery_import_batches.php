<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_import_batches', function (Blueprint $table): void {
            $table->text('klinker_daily_overrides')->nullable()->after('petrokok_route_preference');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_import_batches', function (Blueprint $table): void {
            $table->dropColumn('klinker_daily_overrides');
        });
    }
};

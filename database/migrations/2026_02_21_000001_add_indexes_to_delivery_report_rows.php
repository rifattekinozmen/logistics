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
        Schema::table('delivery_report_rows', function (Blueprint $table) {
            // Index for batch queries
            $table->index('delivery_import_batch_id', 'idx_delivery_report_rows_batch_id');

            // Composite index for batch + created_at (common filtering pattern)
            $table->index(['delivery_import_batch_id', 'created_at'], 'idx_delivery_report_rows_batch_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_report_rows', function (Blueprint $table) {
            $table->dropIndex('idx_delivery_report_rows_batch_id');
            $table->dropIndex('idx_delivery_report_rows_batch_created');
        });
    }
};

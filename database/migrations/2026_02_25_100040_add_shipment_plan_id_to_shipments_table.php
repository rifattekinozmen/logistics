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
        Schema::table('shipments', function (Blueprint $table) {
            // SQL Server multiple cascade paths kısıtını tetiklememek için
            // burada ON DELETE SET NULL / CASCADE kullanmıyoruz.
            $table->unsignedBigInteger('shipment_plan_id')
                ->nullable()
                ->after('id');

            $table->foreign('shipment_plan_id')
                ->references('id')
                ->on('shipment_plans');

            $table->index('shipment_plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign('shipments_shipment_plan_id_foreign');
            $table->dropColumn('shipment_plan_id');
        });
    }
};

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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('planned_at')->nullable()->after('delivered_at');
            $table->timestamp('invoiced_at')->nullable()->after('planned_at');
            $table->string('sap_order_number', 20)->nullable()->after('order_number');
            $table->index('sap_order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['sap_order_number']);
            $table->dropColumn(['planned_at', 'invoiced_at', 'sap_order_number']);
        });
    }
};

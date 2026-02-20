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
            $table->decimal('freight_price', 10, 2)->nullable()->after('total_price');
            $table->unsignedBigInteger('pricing_condition_id')->nullable()->after('freight_price');

            $table->index('pricing_condition_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['pricing_condition_id']);
            $table->dropColumn(['freight_price', 'pricing_condition_id']);
        });
    }
};

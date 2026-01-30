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
        Schema::table('delivery_numbers', function (Blueprint $table) {
            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_numbers', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
        });
    }
};

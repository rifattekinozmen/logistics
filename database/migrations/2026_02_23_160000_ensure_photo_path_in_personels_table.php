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
        if (Schema::hasColumn('personels', 'photo_path')) {
            return;
        }

        Schema::table('personels', function (Blueprint $table) {
            $table->string('photo_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('personels', 'photo_path')) {
            return;
        }

        Schema::table('personels', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};

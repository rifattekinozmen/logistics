<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_import_batches', function (Blueprint $table) {
            $table->json('import_errors')->nullable()->after('failed_rows');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_import_batches', function (Blueprint $table) {
            $table->dropColumn('import_errors');
        });
    }
};

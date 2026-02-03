<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_import_batches', function (Blueprint $table) {
            $table->string('invoice_status', 32)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_import_batches', function (Blueprint $table) {
            $table->dropColumn('invoice_status');
        });
    }
};

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
        Schema::create('sap_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('sap_document_id')->index();
            $table->string('operation', 50);
            $table->string('direction', 20)->default('outbound')->comment('outbound,inbound');
            $table->smallInteger('http_status')->nullable();
            $table->string('result', 20)->default('success')->comment('success,error,timeout,skipped');
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_sync_logs');
    }
};

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
        Schema::create('sap_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('local_model_type', 100);
            $table->unsignedBigInteger('local_model_id');
            $table->string('sap_doc_type', 10)->comment('TA=Sales Order, LF=Delivery, FV=Invoice');
            $table->string('sap_doc_number', 30)->nullable();
            $table->string('sap_doc_year', 4)->nullable();
            $table->string('sap_status', 30)->nullable();
            $table->string('sync_status', 20)->default('pending')->comment('pending,synced,error,skipped');
            $table->timestamp('last_synced_at')->nullable();
            $table->text('sync_error')->nullable();
            $table->text('sap_payload')->nullable();
            $table->text('sap_response')->nullable();
            $table->timestamps();

            $table->index(['local_model_type', 'local_model_id']);
            $table->index(['company_id', 'sync_status']);
            $table->index('sap_doc_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_documents');
    }
};

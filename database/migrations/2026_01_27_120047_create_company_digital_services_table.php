<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_digital_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('service_type');
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('added_at')->nullable();
            $table->string('activation_code')->nullable();
            $table->string('gb_label')->nullable();
            $table->string('pk_label')->nullable();
            $table->string('close_request_status')->default('none');
            $table->timestamp('close_requested_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->unsignedInteger('stats_last_24h')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_digital_services');
    }
};


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
        Schema::create('business_partners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('partner_number', 20)->unique();
            $table->string('partner_type', 20); // customer, vendor, carrier, both
            $table->string('name', 255);
            $table->string('short_name', 100)->nullable();
            $table->string('tax_number', 50)->nullable();
            $table->string('tax_office', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('address', 1000)->nullable();
            $table->string('currency', 3)->default('TRY');
            $table->string('payment_terms', 20)->nullable(); // NET30, NET60, NET90, IMMEDIATE
            $table->decimal('credit_limit', 12, 2)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('notes', 2000)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('partner_type');
            $table->index('partner_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_partners');
    }
};

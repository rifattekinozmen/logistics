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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_code', 50)->nullable()->after('company_id');
            $table->string('customer_type', 50)->nullable()->after('customer_code');
            $table->string('priority_level', 50)->nullable()->after('customer_type');
            $table->string('contact_person', 255)->nullable()->after('priority_level');
            $table->string('tax_office', 100)->nullable()->after('tax_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'customer_code',
                'customer_type',
                'priority_level',
                'contact_person',
                'tax_office',
            ]);
        });
    }
};

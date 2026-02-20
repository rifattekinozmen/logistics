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
        Schema::create('e_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->morphs('related'); // related_type, related_id
            $table->uuid('invoice_uuid')->unique();
            $table->string('invoice_type')->default('e-fatura'); // e-fatura, e-arsiv, e-irsaliye
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date');
            $table->string('customer_name');
            $table->string('customer_tax_number')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 3)->default('TRY');
            $table->text('xml_content')->nullable(); // UBL-TR XML
            $table->string('gib_status')->default('pending'); // pending, sent, approved, rejected, error
            $table->text('gib_response')->nullable(); // GIB response JSON
            $table->text('gib_error')->nullable(); // Error message
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'gib_status']);
            $table->index(['invoice_type', 'invoice_date']);
            $table->index('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_invoices');
    }
};

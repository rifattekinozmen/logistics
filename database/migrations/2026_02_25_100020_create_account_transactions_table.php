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
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('e_invoice_id')->nullable()->constrained('e_invoices')->nullOnDelete();
            $table->string('type', 20); // debit, credit, adjustment
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->string('currency', 3)->default('TRY');
            $table->string('description', 1000)->nullable();
            $table->timestamp('transaction_date')->useCurrent();
            $table->timestamps();

            $table->index(['customer_id', 'transaction_date']);
            $table->index(['payment_id', 'e_invoice_id']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};

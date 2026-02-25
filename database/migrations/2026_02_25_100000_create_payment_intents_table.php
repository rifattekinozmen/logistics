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
        Schema::create('payment_intents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('TRY');
            $table->string('payment_method', 50)->nullable(); // credit_card, bank_transfer, account_balance, etc.
            $table->string('status', 50)->default('pending'); // pending, requires_action, approved, failed, cancelled, expired
            $table->string('provider', 100)->nullable(); // iyzico, stripe, manual, etc.
            $table->string('provider_intent_id', 191)->nullable(); // remote intent/payment id
            $table->timestamp('expires_at')->nullable();
            $table->json('meta')->nullable(); // gateway request/response snippets (non-sensitive)
            $table->timestamps();

            $table->index('order_id');
            $table->index(['status', 'payment_method']);
            $table->index(['provider', 'provider_intent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_intents');
    }
};


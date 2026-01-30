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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('related_type', 100); // App\Models\Employee, App\Models\Vehicle, etc.
            $table->unsignedBigInteger('related_id'); // polymorphic
            $table->string('payment_type', 50); // salary, insurance, tax, supplier, customer, etc.
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->tinyInteger('status')->default(0); // 0: bekliyor, 1: ödendi, 2: gecikti, 3: iptal
            $table->string('payment_method', 50)->nullable(); // cash, bank_transfer, etc.
            $table->string('reference_number', 100)->nullable();
            $table->string('notes', 1000)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('no action');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['related_id', 'related_type']);
            $table->index(['due_date', 'status']);
            $table->index('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

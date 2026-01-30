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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('no action');
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('address', 1000)->nullable();
            $table->string('warehouse_type', 50); // main, transit, temporary
            $table->tinyInteger('status')->default(1); // 0: pasif, 1: aktif
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('no action');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};

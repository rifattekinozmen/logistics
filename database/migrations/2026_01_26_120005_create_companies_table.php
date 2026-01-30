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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ticari unvan
            $table->string('short_name')->nullable(); // Kısa isim
            $table->string('tax_office')->nullable(); // Vergi dairesi
            $table->string('tax_number', 50)->nullable(); // Vergi numarası
            $table->string('mersis_no', 20)->nullable(); // MERSIS numarası
            $table->string('trade_registry_no', 50)->nullable(); // Ticaret sicil numarası
            $table->string('currency', 3)->default('TRY'); // Para birimi
            $table->decimal('default_vat_rate', 5, 2)->default(20.00); // Varsayılan KDV oranı
            $table->string('logo_path')->nullable(); // Logo dosya yolu
            $table->string('stamp_path')->nullable(); // Kaşe/İmza dosya yolu
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true); // Aktiflik durumu
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

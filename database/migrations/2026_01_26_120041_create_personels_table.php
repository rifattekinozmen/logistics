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
        Schema::create('personels', function (Blueprint $table) {
            $table->id();
            $table->string('ad_soyad');
            $table->string('email')->unique();
            $table->string('telefon')->nullable();
            $table->string('departman')->nullable();
            $table->string('pozisyon')->nullable();
            $table->date('ise_baslama_tarihi')->nullable();
            $table->decimal('maas', 10, 2)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personels');
    }
};

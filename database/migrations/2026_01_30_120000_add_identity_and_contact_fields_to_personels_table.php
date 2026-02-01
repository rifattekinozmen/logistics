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
        Schema::table('personels', function (Blueprint $table) {
            $table->string('tckn', 11)->nullable()->after('ad_soyad');
            $table->string('kimlik_seri_no', 50)->nullable()->after('tckn');
            $table->string('mobil_telefon', 20)->nullable()->after('telefon');
            $table->string('acil_iletisim', 20)->nullable()->after('mobil_telefon');
            $table->string('anne_adi', 255)->nullable()->after('acil_iletisim');
            $table->string('baba_adi', 255)->nullable()->after('anne_adi');
            $table->date('dogum_tarihi')->nullable()->after('baba_adi');
            $table->string('dogum_yeri', 255)->nullable()->after('dogum_tarihi');
            $table->string('medeni_durum', 50)->nullable()->after('dogum_yeri');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personels', function (Blueprint $table) {
            $table->dropColumn([
                'tckn',
                'kimlik_seri_no',
                'anne_adi',
                'baba_adi',
                'dogum_tarihi',
                'dogum_yeri',
                'medeni_durum',
                'mobil_telefon',
                'acil_iletisim',
            ]);
        });
    }
};

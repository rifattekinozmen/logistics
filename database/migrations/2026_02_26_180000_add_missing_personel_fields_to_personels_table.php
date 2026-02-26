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
        Schema::table('personels', function (Blueprint $table): void {
            // Firma bilgileri
            $table->string('sirket_vergi_no', 50)->nullable()->after('personel_kodu');
            $table->string('sirket_sgk_no', 50)->nullable()->after('sirket_vergi_no');
            $table->string('sirket_sicil_no', 50)->nullable()->after('sirket_sgk_no');
            $table->string('sirket_unvani')->nullable()->after('sirket_sicil_no');

            // Kimlik / pasaport
            $table->string('pasaport_seri_no', 50)->nullable()->after('tckn');

            // Adres detayları
            $table->string('adres_satir_2')->nullable()->after('adres_satir_1');
            $table->string('mahalle')->nullable()->after('district_id');
            $table->string('cadde')->nullable()->after('sokak');

            // İş / durum bilgileri
            $table->string('calisma_durumu', 50)->nullable()->after('aktif');
            $table->date('basvuru_tarihi')->nullable()->after('ise_baslama_tarihi');
            $table->date('referans_tarihi')->nullable()->after('basvuru_tarihi');

            // Askerlik detayları
            $table->string('askerlik_turu', 50)->nullable()->after('askerlik_durumu');
            $table->date('askerlik_baslangic_tarihi')->nullable()->after('askerlik_turu');
            $table->date('askerlik_bitis_tarihi')->nullable()->after('askerlik_baslangic_tarihi');

            // SGK / meslek detayları
            $table->string('sgk_sigorta_kodu', 50)->nullable()->after('sgk_30_gunden_az');
            $table->string('sgk_sigorta_adi')->nullable()->after('sgk_sigorta_kodu');
            $table->string('csgb_is_kolu_kodu', 50)->nullable()->after('sgk_sigorta_adi');
            $table->string('csgb_is_kolu_adi')->nullable()->after('csgb_is_kolu_kodu');
            $table->string('kanun_2821_gorev_kodu', 50)->nullable()->after('csgb_is_kolu_adi');
            $table->string('kanun_2821_gorev_adi')->nullable()->after('kanun_2821_gorev_kodu');
            $table->string('meslek_kodu', 50)->nullable()->after('kanun_2821_gorev_adi');
            $table->string('meslek_adi')->nullable()->after('meslek_kodu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personels', function (Blueprint $table): void {
            $table->dropColumn([
                'sirket_vergi_no',
                'sirket_sgk_no',
                'sirket_sicil_no',
                'sirket_unvani',
                'pasaport_seri_no',
                'adres_satir_2',
                'mahalle',
                'cadde',
                'calisma_durumu',
                'basvuru_tarihi',
                'referans_tarihi',
                'askerlik_turu',
                'askerlik_baslangic_tarihi',
                'askerlik_bitis_tarihi',
                'sgk_sigorta_kodu',
                'sgk_sigorta_adi',
                'csgb_is_kolu_kodu',
                'csgb_is_kolu_adi',
                'kanun_2821_gorev_kodu',
                'kanun_2821_gorev_adi',
                'meslek_kodu',
                'meslek_adi',
            ]);
        });
    }
};


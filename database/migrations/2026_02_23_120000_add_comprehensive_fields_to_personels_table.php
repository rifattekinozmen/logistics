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
            // Kişisel
            $table->string('personel_kodu', 50)->nullable()->after('id');
            $table->string('kan_grubu', 10)->nullable()->after('medeni_durum');
            $table->string('cinsiyet', 20)->nullable()->after('kan_grubu');
            $table->unsignedTinyInteger('cocuk_sayisi')->nullable()->after('cinsiyet');

            // Adres
            $table->string('adres_satir_1')->nullable()->after('cocuk_sayisi');
            $table->foreignId('country_id')->nullable()->after('adres_satir_1')->constrained('countries')->onDelete('no action');
            $table->foreignId('city_id')->nullable()->after('country_id')->constrained('cities')->onDelete('no action');
            $table->foreignId('district_id')->nullable()->after('city_id')->constrained('districts')->onDelete('no action');
            $table->string('bulvar')->nullable()->after('district_id');
            $table->string('sokak')->nullable()->after('bulvar');
            $table->string('dis_kapi', 20)->nullable()->after('sokak');
            $table->string('ic_kapi', 20)->nullable()->after('dis_kapi');
            $table->string('posta_kodu', 20)->nullable()->after('ic_kapi');

            // İş
            $table->date('sgk_baslangic_tarihi')->nullable()->after('ise_baslama_tarihi');

            // Kimlik
            $table->string('cilt_no', 20)->nullable()->after('kimlik_seri_no');
            $table->string('aile_sira_no', 20)->nullable()->after('cilt_no');
            $table->string('sira_no', 20)->nullable()->after('aile_sira_no');
            $table->string('cuzdan_kayit_no', 20)->nullable()->after('sira_no');
            $table->date('verilis_tarihi')->nullable()->after('cuzdan_kayit_no');

            // Eğitim
            $table->string('tahsil_durumu', 50)->nullable()->after('verilis_tarihi');
            $table->string('mezun_okul')->nullable()->after('tahsil_durumu');
            $table->string('mezun_bolum')->nullable()->after('mezun_okul');
            $table->date('mezuniyet_tarihi')->nullable()->after('mezun_bolum');
            $table->string('bildigi_dil', 100)->nullable()->after('mezuniyet_tarihi');

            // Askerlik
            $table->string('askerlik_durumu', 50)->nullable()->after('bildigi_dil');

            // SGK
            $table->boolean('sgk_yaslilik_ayligi')->nullable()->after('askerlik_durumu');
            $table->boolean('sgk_30_gunden_az')->nullable()->after('sgk_yaslilik_ayligi');

            // Banka
            $table->string('banka_adi', 100)->nullable()->after('sgk_30_gunden_az');
            $table->string('sube_kodu', 20)->nullable()->after('banka_adi');
            $table->string('hesap_no', 50)->nullable()->after('sube_kodu');
            $table->string('maas_odeme_turu', 50)->nullable()->after('hesap_no');
            $table->string('iban', 34)->nullable()->after('maas_odeme_turu');

            // Notlar
            $table->text('notlar')->nullable()->after('aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personels', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['city_id']);
            $table->dropForeign(['district_id']);

            $table->dropColumn([
                'personel_kodu',
                'kan_grubu',
                'cinsiyet',
                'cocuk_sayisi',
                'adres_satir_1',
                'country_id',
                'city_id',
                'district_id',
                'bulvar',
                'sokak',
                'dis_kapi',
                'ic_kapi',
                'posta_kodu',
                'sgk_baslangic_tarihi',
                'cilt_no',
                'aile_sira_no',
                'sira_no',
                'cuzdan_kayit_no',
                'verilis_tarihi',
                'tahsil_durumu',
                'mezun_okul',
                'mezun_bolum',
                'mezuniyet_tarihi',
                'bildigi_dil',
                'askerlik_durumu',
                'sgk_yaslilik_ayligi',
                'sgk_30_gunden_az',
                'banka_adi',
                'sube_kodu',
                'hesap_no',
                'maas_odeme_turu',
                'iban',
                'notlar',
            ]);
        });
    }
};

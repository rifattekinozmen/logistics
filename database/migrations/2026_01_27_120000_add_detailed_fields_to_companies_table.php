<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Ülke, İl, İlçe ilişkileri - SQL Server için no action kullanıyoruz
            if (! Schema::hasColumn('companies', 'country_id')) {
                $table->foreignId('country_id')->nullable();
            }
            if (! Schema::hasColumn('companies', 'city_id')) {
                $table->foreignId('city_id')->nullable();
            }
            if (! Schema::hasColumn('companies', 'district_id')) {
                $table->foreignId('district_id')->nullable();
            }
            
            // İletişim bilgileri
            if (! Schema::hasColumn('companies', 'mobile_phone')) {
                $table->string('mobile_phone', 20)->nullable();
            }
            if (! Schema::hasColumn('companies', 'landline_phone')) {
                $table->string('landline_phone', 20)->nullable();
            }
            if (! Schema::hasColumn('companies', 'fax')) {
                $table->string('fax', 20)->nullable();
            }
            if (! Schema::hasColumn('companies', 'authorized_email')) {
                $table->string('authorized_email')->nullable();
            }
            if (! Schema::hasColumn('companies', 'website')) {
                $table->string('website')->nullable();
            }
            
            // Adres bilgileri
            if (! Schema::hasColumn('companies', 'address')) {
                $table->text('address')->nullable();
            }
            if (! Schema::hasColumn('companies', 'postal_code')) {
                $table->string('postal_code', 10)->nullable();
            }
            if (! Schema::hasColumn('companies', 'headquarters_city')) {
                $table->string('headquarters_city')->nullable();
            }
            
            // Yetkili kişi bilgileri
            if (! Schema::hasColumn('companies', 'authorized_person_name')) {
                $table->string('authorized_person_name')->nullable();
            }
            if (! Schema::hasColumn('companies', 'title')) {
                $table->string('title')->nullable(); // Ünvan
            }
            
            // Vergi dairesi ili
            if (! Schema::hasColumn('companies', 'tax_office_city_id')) {
                $table->foreignId('tax_office_city_id')->nullable();
            }
            
            // e-Fatura ve e-İrsaliye bilgileri
            if (! Schema::hasColumn('companies', 'e_invoice_pk_tag')) {
                $table->string('e_invoice_pk_tag')->nullable();
            }
            if (! Schema::hasColumn('companies', 'e_waybill_pk_tag')) {
                $table->string('e_waybill_pk_tag')->nullable();
            }
            if (! Schema::hasColumn('companies', 'e_invoice_gb_tag')) {
                $table->string('e_invoice_gb_tag')->nullable();
            }
            if (! Schema::hasColumn('companies', 'e_waybill_gb_tag')) {
                $table->string('e_waybill_gb_tag')->nullable();
            }
            
            // Sermaye ve API
            if (! Schema::hasColumn('companies', 'capital_amount')) {
                $table->decimal('capital_amount', 15, 2)->nullable();
            }
            if (! Schema::hasColumn('companies', 'api_key')) {
                $table->string('api_key')->nullable();
            }
        });

        // Foreign key constraint'leri SQL Server için NO ACTION ile ekliyoruz
        // SQL Server cascade path sorununu önlemek için raw SQL kullanıyoruz
        if (DB::getDriverName() === 'sqlsrv') {
            // Constraint'leri try-catch ile ekliyoruz (zaten varsa hata vermez)
            if (Schema::hasColumn('companies', 'country_id')) {
                try {
                    DB::statement('ALTER TABLE companies ADD CONSTRAINT companies_country_id_foreign FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE NO ACTION');
                } catch (\Exception $e) {
                    // Constraint zaten varsa devam et
                }
            }
            if (Schema::hasColumn('companies', 'city_id')) {
                try {
                    DB::statement('ALTER TABLE companies ADD CONSTRAINT companies_city_id_foreign FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE NO ACTION');
                } catch (\Exception $e) {
                    // Constraint zaten varsa devam et
                }
            }
            if (Schema::hasColumn('companies', 'district_id')) {
                try {
                    DB::statement('ALTER TABLE companies ADD CONSTRAINT companies_district_id_foreign FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE NO ACTION');
                } catch (\Exception $e) {
                    // Constraint zaten varsa devam et
                }
            }
            if (Schema::hasColumn('companies', 'tax_office_city_id')) {
                try {
                    DB::statement('ALTER TABLE companies ADD CONSTRAINT companies_tax_office_city_id_foreign FOREIGN KEY (tax_office_city_id) REFERENCES cities(id) ON DELETE NO ACTION');
                } catch (\Exception $e) {
                    // Constraint zaten varsa devam et
                }
            }
        } else {
            Schema::table('companies', function (Blueprint $table) {
                if (Schema::hasColumn('companies', 'country_id')) {
                    try {
                        $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
                    } catch (\Exception $e) {
                        // Constraint zaten varsa devam et
                    }
                }
                if (Schema::hasColumn('companies', 'city_id')) {
                    try {
                        $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
                    } catch (\Exception $e) {
                        // Constraint zaten varsa devam et
                    }
                }
                if (Schema::hasColumn('companies', 'district_id')) {
                    try {
                        $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
                    } catch (\Exception $e) {
                        // Constraint zaten varsa devam et
                    }
                }
                if (Schema::hasColumn('companies', 'tax_office_city_id')) {
                    try {
                        $table->foreign('tax_office_city_id')->references('id')->on('cities')->onDelete('set null');
                    } catch (\Exception $e) {
                        // Constraint zaten varsa devam et
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Foreign key constraint'leri sil
            if (DB::getDriverName() === 'sqlsrv') {
                DB::statement('ALTER TABLE companies DROP CONSTRAINT IF EXISTS companies_country_id_foreign');
                DB::statement('ALTER TABLE companies DROP CONSTRAINT IF EXISTS companies_city_id_foreign');
                DB::statement('ALTER TABLE companies DROP CONSTRAINT IF EXISTS companies_district_id_foreign');
                DB::statement('ALTER TABLE companies DROP CONSTRAINT IF EXISTS companies_tax_office_city_id_foreign');
            } else {
                $table->dropForeign(['country_id']);
                $table->dropForeign(['city_id']);
                $table->dropForeign(['district_id']);
                $table->dropForeign(['tax_office_city_id']);
            }
            
            $table->dropColumn([
                'country_id',
                'city_id',
                'district_id',
                'mobile_phone',
                'landline_phone',
                'fax',
                'authorized_email',
                'website',
                'address',
                'postal_code',
                'headquarters_city',
                'authorized_person_name',
                'title',
                'tax_office_city_id',
                'e_invoice_pk_tag',
                'e_waybill_pk_tag',
                'e_invoice_gb_tag',
                'e_waybill_gb_tag',
                'capital_amount',
                'api_key',
            ]);
        });
    }
};

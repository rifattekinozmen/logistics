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
        Schema::table('companies', function (Blueprint $table) {
            // Eğer kolon yoksa ekle (SQL Server'da after() çalışmaz, bu yüzden kaldırdık)
            if (! Schema::hasColumn('companies', 'short_name')) {
                $table->string('short_name')->nullable();
            }
            if (! Schema::hasColumn('companies', 'tax_office')) {
                $table->string('tax_office')->nullable();
            }
            if (! Schema::hasColumn('companies', 'mersis_no')) {
                $table->string('mersis_no', 20)->nullable();
            }
            if (! Schema::hasColumn('companies', 'trade_registry_no')) {
                $table->string('trade_registry_no', 50)->nullable();
            }
            if (! Schema::hasColumn('companies', 'currency')) {
                $table->string('currency', 3)->default('TRY');
            }
            if (! Schema::hasColumn('companies', 'default_vat_rate')) {
                $table->decimal('default_vat_rate', 5, 2)->default(20.00);
            }
            if (! Schema::hasColumn('companies', 'logo_path')) {
                $table->string('logo_path')->nullable();
            }
            if (! Schema::hasColumn('companies', 'stamp_path')) {
                $table->string('stamp_path')->nullable();
            }
            
            // is_active kolonu yoksa ekle
            if (! Schema::hasColumn('companies', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $columns = [
                'short_name',
                'tax_office',
                'mersis_no',
                'trade_registry_no',
                'currency',
                'default_vat_rate',
                'logo_path',
                'stamp_path',
                'is_active',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

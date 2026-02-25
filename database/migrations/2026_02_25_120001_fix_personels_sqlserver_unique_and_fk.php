<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SQL Server: personel_kodu nullable kolonda çoklu NULL için mevcut unique index
     * kaldırılıp filtered unique index eklenir (sadece personel_kodu IS NOT NULL için tekil).
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlsrv') {
            return;
        }

        try {
            Schema::table('personels', function (Blueprint $table) {
                $table->dropUnique('personels_personel_kodu_unique');
            });
        } catch (Throwable) {
            // Index yoksa veya farklı isimle varsa devam et
        }

        DB::statement("
            IF NOT EXISTS (
                SELECT 1 FROM sys.indexes
                WHERE name = N'personels_personel_kodu_unique_filtered'
                AND object_id = OBJECT_ID(N'personels')
            )
            CREATE UNIQUE NONCLUSTERED INDEX personels_personel_kodu_unique_filtered
            ON personels(personel_kodu)
            WHERE personel_kodu IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlsrv') {
            return;
        }

        DB::statement("
            IF EXISTS (
                SELECT 1 FROM sys.indexes
                WHERE name = N'personels_personel_kodu_unique_filtered'
                AND object_id = OBJECT_ID(N'personels')
            )
            DROP INDEX personels_personel_kodu_unique_filtered ON personels
        ");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Puantaj personel_id ile çalıştığı için (employee_id NULL) aynı tarihte
     * birden fazla personel kaydı eklenebilmeli. (employee_id, attendance_date)
     * unique kaldırılıyor; tekillik (personel_id, attendance_date) ile sağlanıyor.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $indexName = 'personnel_attendance_employee_id_attendance_date_unique';

        if ($driver === 'sqlsrv') {
            if ($this->indexExists('personnel_attendance', $indexName)) {
                DB::statement("DROP INDEX [{$indexName}] ON [personnel_attendance]");
            }

            return;
        }

        Schema::table('personnel_attendance', function (Blueprint $table) {
            $table->dropUnique(['employee_id', 'attendance_date']);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select(
            'SELECT 1 FROM sys.indexes WHERE object_id = OBJECT_ID(?) AND name = ?',
            ['dbo.'.$table, $indexName]
        );

        return count($result) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnel_attendance', function (Blueprint $table) {
            $table->unique(['employee_id', 'attendance_date']);
        });
    }
};

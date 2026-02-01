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
        if (Schema::hasColumn('personnel_attendance', 'personel_id')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        Schema::table('personnel_attendance', function (Blueprint $table) {
            $table->dropUnique(['employee_id', 'attendance_date']);
        });
        Schema::table('personnel_attendance', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE personnel_attendance MODIFY employee_id BIGINT UNSIGNED NULL');
        }
        if ($driver === 'sqlsrv') {
            DB::statement('ALTER TABLE personnel_attendance ALTER COLUMN employee_id BIGINT NULL');
        }

        Schema::table('personnel_attendance', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->unique(['employee_id', 'attendance_date']);
        });

        Schema::table('personnel_attendance', function (Blueprint $table) {
            $table->foreignId('personel_id')->nullable()->after('id')->constrained('personels')->onDelete('cascade');
            $table->unique(['personel_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnel_attendance', function (Blueprint $table) {
            $table->dropUnique(['personel_id', 'attendance_date']);
            $table->dropForeign(['personel_id']);
        });
    }
};

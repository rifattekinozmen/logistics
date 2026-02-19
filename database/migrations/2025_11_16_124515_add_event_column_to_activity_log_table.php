<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('activitylog.table_name', 'activity_log');
        $connection = config('activitylog.database_connection');

        if (empty($tableName)) {
            $tableName = 'activity_log';
        }

        $schema = $connection
            ? Schema::connection($connection)
            : Schema::connection(config('database.default'));

        $schema->table($tableName, function (Blueprint $table) use ($connection, $tableName) {
            $checkSchema = $connection
                ? Schema::connection($connection)
                : Schema::connection(config('database.default'));

            if (! $checkSchema->hasColumn($tableName, 'event')) {
                $table->string('event')->nullable()->after('subject_type');
            }
        });
    }

    public function down(): void
    {
        $tableName = config('activitylog.table_name', 'activity_log');
        $connection = config('activitylog.database_connection');

        if (empty($tableName)) {
            $tableName = 'activity_log';
        }

        $schema = $connection
            ? Schema::connection($connection)
            : Schema::connection(config('database.default'));

        $schema->table($tableName, function (Blueprint $table) {
            $table->dropColumn('event');
        });
    }
};

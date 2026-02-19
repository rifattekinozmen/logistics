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

        $schema->create($tableName, function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->text('properties')->nullable();
            $table->timestamps();

            $table->index('log_name');
            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
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

        $schema->dropIfExists($tableName);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('device_token', 500)->nullable()->after('remember_token');
            $table->string('device_type', 20)->nullable()->after('device_token');
            $table->timestamp('device_token_updated_at')->nullable()->after('device_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['device_token', 'device_type', 'device_token_updated_at']);
        });
    }
};

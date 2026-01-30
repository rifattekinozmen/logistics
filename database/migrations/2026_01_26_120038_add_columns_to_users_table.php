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
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        
        if ($driver === 'sqlsrv') {
            // SQL Server için doğrudan SQL komutları kullan
            if (! Schema::hasColumn('users', 'username')) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE users ADD username NVARCHAR(50) NULL');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE users ADD phone NVARCHAR(20) NULL');
            }
            if (! Schema::hasColumn('users', 'status')) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE users ADD status TINYINT NOT NULL DEFAULT 1');
            }
            if (! Schema::hasColumn('users', 'last_login_at')) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE users ADD last_login_at DATETIME2 NULL');
            }
            if (! Schema::hasColumn('users', 'deleted_at')) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE users ADD deleted_at DATETIME2 NULL');
            }

            // SQL Server için filtered unique index (nullable kolonlar için)
            if (Schema::hasColumn('users', 'username')) {
                try {
                    \Illuminate\Support\Facades\DB::statement('
                        IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = \'users_username_unique\' AND object_id = OBJECT_ID(\'users\'))
                        CREATE UNIQUE NONCLUSTERED INDEX users_username_unique 
                        ON users(username) 
                        WHERE username IS NOT NULL
                    ');
                } catch (\Exception $e) {
                    // Index zaten varsa veya oluşturulamazsa devam et
                }
            }
        } else {
            // Diğer veritabanları için normal Schema kullan
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'username')) {
                    $table->string('username', 50)->nullable()->unique();
                }
                if (! Schema::hasColumn('users', 'phone')) {
                    $table->string('phone', 20)->nullable();
                }
                if (! Schema::hasColumn('users', 'status')) {
                    $table->tinyInteger('status')->default(1);
                }
                if (! Schema::hasColumn('users', 'last_login_at')) {
                    $table->timestamp('last_login_at')->nullable();
                }
                if (! Schema::hasColumn('users', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'phone', 'status', 'last_login_at', 'deleted_at']);
        });
    }
};

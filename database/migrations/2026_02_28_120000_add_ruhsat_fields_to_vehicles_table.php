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
        Schema::table('vehicles', function (Blueprint $table): void {
            $table->date('first_registration_date')->nullable()->after('vin_number');
            $table->string('registration_sequence_no', 50)->nullable()->after('first_registration_date');
            $table->date('registration_date')->nullable()->after('registration_sequence_no');
            $table->decimal('net_weight_kg', 12, 2)->nullable()->after('capacity_m3');
            $table->decimal('train_weight_kg', 12, 2)->nullable()->after('net_weight_kg');
            $table->decimal('trailer_max_weight_kg', 12, 2)->nullable()->after('train_weight_kg');
            $table->unsignedSmallInteger('seat_count')->nullable()->after('trailer_max_weight_kg');
            $table->unsignedSmallInteger('standing_passenger_count')->nullable()->after('seat_count');
            $table->unsignedInteger('engine_displacement_cm3')->nullable()->after('standing_passenger_count');
            $table->decimal('engine_power_kw', 8, 2)->nullable()->after('engine_displacement_cm3');
            $table->string('usage_purpose', 100)->nullable()->after('engine_power_kw');
            $table->string('type_approval_no', 100)->nullable()->after('usage_purpose');
            $table->string('owner_id_tax_no', 50)->nullable()->after('notes');
            $table->string('owner_surname_trade_name', 255)->nullable()->after('owner_id_tax_no');
            $table->string('owner_first_name', 100)->nullable()->after('owner_surname_trade_name');
            $table->text('owner_address')->nullable()->after('owner_first_name');
            $table->text('rights_holders')->nullable()->after('owner_address');
            $table->date('notary_sale_date')->nullable()->after('rights_holders');
            $table->string('notary_sale_no', 50)->nullable()->after('notary_sale_date');
            $table->string('notary_name', 255)->nullable()->after('notary_sale_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table): void {
            $table->dropColumn([
                'first_registration_date',
                'registration_sequence_no',
                'registration_date',
                'net_weight_kg',
                'train_weight_kg',
                'trailer_max_weight_kg',
                'seat_count',
                'standing_passenger_count',
                'engine_displacement_cm3',
                'engine_power_kw',
                'usage_purpose',
                'type_approval_no',
                'owner_id_tax_no',
                'owner_surname_trade_name',
                'owner_first_name',
                'owner_address',
                'rights_holders',
                'notary_sale_date',
                'notary_sale_no',
                'notary_name',
            ]);
        });
    }
};

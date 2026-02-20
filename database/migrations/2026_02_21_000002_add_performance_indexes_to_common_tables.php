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
        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            // Composite index for company filtering with status and date
            $table->index(['company_id', 'status', 'created_at'], 'idx_orders_company_status_created');

            // Index for customer queries
            $table->index(['customer_id', 'status'], 'idx_orders_customer_status');

            // Index for date range queries
            $table->index('planned_delivery_date', 'idx_orders_planned_delivery');
        });

        // Shipments table indexes
        Schema::table('shipments', function (Blueprint $table) {
            // Composite index for company filtering
            $table->index(['status', 'pickup_date'], 'idx_shipments_status_pickup');

            // Index for vehicle queries
            $table->index(['vehicle_id', 'status'], 'idx_shipments_vehicle_status');

            // Index for driver queries
            $table->index(['driver_id', 'status'], 'idx_shipments_driver_status');

            // Index for order relationship
            $table->index('order_id', 'idx_shipments_order_id');
        });

        // Delivery numbers table indexes
        Schema::table('delivery_numbers', function (Blueprint $table) {
            // Composite index for batch + location
            $table->index(['delivery_import_batch_id', 'location_id'], 'idx_delivery_numbers_batch_location');

            // Index for location queries
            $table->index('location_id', 'idx_delivery_numbers_location');
        });

        // Customers table indexes
        Schema::table('customers', function (Blueprint $table) {
            // Index for business partner lookup
            $table->index('business_partner_id', 'idx_customers_business_partner');

            // Index for status queries
            $table->index(['status', 'created_at'], 'idx_customers_status_created');
        });

        // Payments table indexes
        Schema::table('payments', function (Blueprint $table) {
            // Index for polymorphic relationship
            $table->index(['related_type', 'related_id'], 'idx_payments_related');

            // Index for due date queries
            $table->index(['status', 'due_date'], 'idx_payments_status_due');

            // Index for payment date
            $table->index('paid_date', 'idx_payments_paid_date');
        });

        // Employees table indexes
        Schema::table('employees', function (Blueprint $table) {
            // Index for branch queries
            $table->index(['branch_id', 'status'], 'idx_employees_branch_status');

            // Index for position queries
            $table->index('position_id', 'idx_employees_position');
        });

        // Vehicles table indexes
        Schema::table('vehicles', function (Blueprint $table) {
            // Index for branch queries
            $table->index(['branch_id', 'status'], 'idx_vehicles_branch_status');

            // Index for vehicle type filtering
            $table->index(['vehicle_type', 'status'], 'idx_vehicles_type_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_company_status_created');
            $table->dropIndex('idx_orders_customer_status');
            $table->dropIndex('idx_orders_planned_delivery');
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->dropIndex('idx_shipments_status_pickup');
            $table->dropIndex('idx_shipments_vehicle_status');
            $table->dropIndex('idx_shipments_driver_status');
            $table->dropIndex('idx_shipments_order_id');
        });

        Schema::table('delivery_numbers', function (Blueprint $table) {
            $table->dropIndex('idx_delivery_numbers_batch_location');
            $table->dropIndex('idx_delivery_numbers_location');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customers_business_partner');
            $table->dropIndex('idx_customers_status_created');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_related');
            $table->dropIndex('idx_payments_status_due');
            $table->dropIndex('idx_payments_paid_date');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_employees_branch_status');
            $table->dropIndex('idx_employees_position');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex('idx_vehicles_branch_status');
            $table->dropIndex('idx_vehicles_type_status');
        });
    }
};

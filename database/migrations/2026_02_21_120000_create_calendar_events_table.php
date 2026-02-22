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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('event_type', [
                'document',
                'payment',
                'maintenance',
                'leave',
                'delivery',
                'meeting',
                'inspection',
                'other',
            ]);

            // Polymorphic relationship
            $table->string('related_type')->nullable();
            $table->bigInteger('related_id')->nullable();
            
            // Date and time
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_all_day')->default(false);
            
            // Priority and status
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'completed', 'overdue', 'cancelled'])->default('pending');
            
            // Visual settings
            $table->string('color', 20)->default('#3B82F6');
            
            // Notification tracking
            $table->boolean('reminder_sent')->default(false);
            $table->dateTime('reminder_sent_at')->nullable();
            
            // Multi-tenant
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['start_date', 'end_date'], 'idx_calendar_events_dates');
            $table->index(['event_type', 'status'], 'idx_calendar_events_type_status');
            $table->index(['company_id', 'start_date'], 'idx_calendar_events_company_date');
            $table->index(['related_type', 'related_id'], 'idx_calendar_events_related');
            $table->index(['status', 'reminder_sent', 'start_date'], 'idx_calendar_events_reminders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};

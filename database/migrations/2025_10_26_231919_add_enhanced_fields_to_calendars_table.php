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
        Schema::table('calendars', function (Blueprint $table) {
            // Task integration
            $table->foreignId('task_id')->nullable()->constrained('tasks')->onDelete('set null');

            // Priority management
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            // Event categorization
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();

            // Enhanced recurrence with more options
            $table->json('recurrence_rule')->nullable(); // Store complex recurrence rules
            $table->dateTime('recurrence_end_date')->nullable();
            $table->integer('recurrence_count')->nullable(); // End after X occurrences
            $table->string('recurrence_parent_id')->nullable(); // For recurring event series

            // Reminder and notification settings
            $table->json('reminder_settings')->nullable(); // Email, SMS, push notification preferences
            $table->integer('reminder_minutes_before')->default(15);

            // Event metadata
            $table->decimal('duration_hours', 5, 2)->nullable(); // Calculated duration
            $table->boolean('is_recurring')->default(false);
            $table->boolean('is_exception')->default(false); // For modified recurring instances

            // Additional fields for professional features
            $table->text('notes')->nullable();
            $table->string('meeting_link')->nullable(); // For virtual meetings
            $table->decimal('cost', 10, 2)->nullable(); // Event cost if applicable
            $table->json('custom_fields')->nullable(); // Flexible custom data

            // Indexes for performance
            $table->index(['task_id']);
            $table->index(['priority', 'start_time']);
            $table->index(['category']);
            $table->index(['is_recurring']);
            $table->index(['recurrence_parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendars', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['task_id']);
            $table->dropIndex(['priority', 'start_time']);
            $table->dropIndex(['category']);
            $table->dropIndex(['is_recurring']);
            $table->dropIndex(['recurrence_parent_id']);

            // Drop columns
            $table->dropForeign(['task_id']);
            $table->dropColumn([
                'task_id',
                'priority',
                'category',
                'subcategory',
                'recurrence_rule',
                'recurrence_end_date',
                'recurrence_count',
                'recurrence_parent_id',
                'reminder_settings',
                'reminder_minutes_before',
                'duration_hours',
                'is_recurring',
                'is_exception',
                'notes',
                'meeting_link',
                'cost',
                'custom_fields'
            ]);
        });
    }
};

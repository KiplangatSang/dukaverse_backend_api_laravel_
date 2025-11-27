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
        Schema::create('calendar_notifications', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('calendar_id')->constrained('calendars')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Notification type and content
            $table->enum('type', [
                'reminder',
                'update',
                'cancellation',
                'invitation',
                'response',
                'overdue',
                'conflict'
            ]);
            $table->string('title');
            $table->text('message');

            // Notification settings
            $table->enum('channel', ['email', 'sms', 'push', 'in_app'])->default('in_app');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            // Scheduling
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();

            // Status tracking
            $table->enum('status', ['pending', 'sent', 'failed', 'cancelled'])->default('pending');
            $table->text('error_message')->nullable();

            // Metadata
            $table->json('metadata')->nullable(); // Additional context data
            $table->integer('retry_count')->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['calendar_id', 'type']);
            $table->index(['scheduled_at']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_notifications');
    }
};

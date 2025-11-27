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
        Schema::create('calendar_attendees', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('calendar_id')->constrained('calendars')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Attendee status and role
            $table->enum('status', ['pending', 'accepted', 'declined', 'tentative'])->default('pending');
            $table->enum('role', ['organizer', 'attendee', 'optional'])->default('attendee');

            // Response tracking
            $table->timestamp('responded_at')->nullable();
            $table->text('response_message')->nullable();

            // Notification preferences for this event
            $table->boolean('notify_reminders')->default(true);
            $table->boolean('notify_updates')->default(true);

            $table->timestamps();

            // Unique constraint to prevent duplicate attendees
            $table->unique(['calendar_id', 'user_id']);

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['calendar_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_attendees');
    }
};

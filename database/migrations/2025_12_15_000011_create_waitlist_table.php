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
        Schema::create('waitlist', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');

            // Customer information
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();

            // Waitlist details
            $table->integer('party_size');
            $table->integer('position');
            $table->integer('estimated_wait_time')->nullable(); // in minutes

            // Preferences
            $table->string('preferred_section')->nullable();
            $table->json('table_preferences')->nullable();
            $table->text('notes')->nullable();

            // Status
            $table->enum('status', [
                'waiting',
                'notified',
                'seated',
                'cancelled',
                'no_show'
            ])->default('waiting');

            // Notification
            $table->json('notify_via')->nullable(); // ['sms', 'call', 'email']
            $table->dateTime('notified_at')->nullable();
            $table->foreignId('notified_by')->nullable()->constrained('users');
            $table->foreignId('table_id')->nullable()->constrained()->onDelete('set null');

            // Seating
            $table->dateTime('seated_at')->nullable();
            $table->foreignId('seated_by')->nullable()->constrained('users');

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['restaurant_id', 'status', 'position']);
            $table->index(['restaurant_id', 'created_at']);
            $table->index('customer_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waitlist');
    }
};

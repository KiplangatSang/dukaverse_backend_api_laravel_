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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->nullable()->constrained()->onDelete('set null');

            // Customer information
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();

            // Reservation details
            $table->integer('party_size');
            $table->dateTime('reservation_datetime');
            $table->integer('duration_minutes')->default(120);

            // Status
            $table->enum('status', [
                'pending',
                'confirmed',
                'seated',
                'completed',
                'cancelled',
                'no_show'
            ])->default('pending');

            // Preferences
            $table->string('preferred_section')->nullable();
            $table->json('table_preferences')->nullable();
            $table->text('special_requests')->nullable();

            // Deposit
            $table->boolean('deposit_required')->default(false);
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->boolean('deposit_paid')->default(false);
            $table->dateTime('deposit_paid_at')->nullable();

            // Confirmation
            $table->string('confirmation_code', 10)->unique();
            $table->string('reservation_number')->unique();

            // Notifications
            $table->json('notification_preferences')->nullable();
            $table->dateTime('reminder_sent_at')->nullable();

            // Check-in
            $table->dateTime('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users');
            $table->integer('actual_party_size')->nullable();

            // Cancellation
            $table->string('cancellation_reason')->nullable();
            $table->text('cancellation_notes')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->boolean('refund_issued')->default(false);
            $table->decimal('refund_amount', 10, 2)->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['restaurant_id', 'reservation_datetime']);
            $table->index(['restaurant_id', 'status']);
            $table->index(['table_id', 'reservation_datetime']);
            $table->index('customer_phone');
            $table->index('confirmation_code');
            $table->index('reservation_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};

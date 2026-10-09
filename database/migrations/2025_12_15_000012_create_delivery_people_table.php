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
        Schema::create('delivery_people', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Personal Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->text('bio')->nullable();

            // Location
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Kenya');

            // Profile Images
            $table->string('profile_photo')->nullable();
            $table->string('id_photo')->nullable();
            $table->string('license_photo')->nullable();

            // Status & Verification
            $table->enum('status', [
                'pending',
                'active',
                'suspended',
                'banned',
                'inactive'
            ])->default('pending');

            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');

            $table->boolean('is_accredited')->default(false);
            $table->timestamp('accredited_at')->nullable();
            $table->foreignId('accredited_by')->nullable()->constrained('users');

            // Ratings & Performance
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('total_deliveries')->default(0);
            $table->integer('completed_deliveries')->default(0);
            $table->integer('cancelled_deliveries')->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0.00);

            // Availability
            $table->boolean('is_online')->default(false);
            $table->boolean('is_available')->default(false);
            $table->timestamp('last_online_at')->nullable();
            $table->timestamp('last_delivery_at')->nullable();

            // Preferences
            $table->json('delivery_preferences')->nullable(); // preferred areas, times, etc.
            $table->json('working_hours')->nullable();
            $table->decimal('max_delivery_distance', 8, 2)->default(50.00); // km

            // Financial
            $table->decimal('wallet_balance', 15, 2)->default(0.00);
            $table->decimal('total_earnings', 15, 2)->default(0.00);
            $table->decimal('pending_payments', 15, 2)->default(0.00);

            // Subscription
            $table->foreignId('current_subscription_id')->nullable()->constrained('delivery_subscriptions');
            $table->timestamp('subscription_expires_at')->nullable();

            // Device & App
            $table->string('device_token')->nullable();
            $table->string('device_type')->nullable(); // android, ios
            $table->string('app_version')->nullable();

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'is_online']);
            $table->index(['latitude', 'longitude']);
            $table->index(['rating', 'total_deliveries']);
            $table->index('phone');
            $table->index('email');
            $table->index(['is_verified', 'is_accredited']);
            $table->index('current_subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_people');
    }
};

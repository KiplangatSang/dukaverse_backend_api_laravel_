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
        Schema::create('delivery_vehicles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('delivery_person_id')->constrained('delivery_people')->onDelete('cascade');

            // Vehicle Information
            $table->string('vehicle_type'); // motorcycle, bicycle, car, van, truck
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('year')->nullable();
            $table->string('color')->nullable();
            $table->string('license_plate')->unique();
            $table->string('registration_number')->nullable();

            // Capacity & Specifications
            $table->decimal('max_weight', 8, 2)->nullable(); // kg
            $table->decimal('max_volume', 8, 2)->nullable(); // cubic meters
            $table->integer('max_packages')->default(1);
            $table->boolean('has_refrigeration')->default(false);
            $table->boolean('has_insurance')->default(false);

            // Documents
            $table->string('registration_document')->nullable();
            $table->string('insurance_document')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->string('vehicle_photo')->nullable();

            // Status
            $table->enum('status', [
                'pending_verification',
                'active',
                'suspended',
                'maintenance',
                'decommissioned'
            ])->default('pending_verification');

            $table->boolean('is_primary')->default(false);

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->timestamps();

            // Indexes
            $table->index(['delivery_person_id', 'status']);
            $table->index(['vehicle_type', 'status']);
            $table->index('license_plate');
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_vehicles');
    }
};

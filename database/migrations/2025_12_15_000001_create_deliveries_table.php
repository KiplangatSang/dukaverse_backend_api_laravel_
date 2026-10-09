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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('rider_id')->nullable()->constrained('employees')->onDelete('set null');

            // Status and timestamps
            $table->enum('status', [
                'pending',
                'assigned',
                'picked_up',
                'in_transit',
                'delivered',
                'cancelled',
                'failed'
            ])->default('pending');

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Location data
            $table->json('pickup_location')->nullable();
            $table->json('delivery_location')->nullable();

            // Delivery details
            $table->decimal('estimated_distance', 8, 2)->nullable();
            $table->integer('estimated_duration')->nullable(); // in minutes
            $table->decimal('actual_distance', 8, 2)->nullable();
            $table->integer('actual_duration')->nullable(); // in minutes
            $table->decimal('fee', 8, 2)->default(0);

            // Ratings and feedback
            $table->tinyInteger('rating')->nullable(); // 1-5
            $table->tinyInteger('rider_rating')->nullable(); // 1-5
            $table->tinyInteger('food_rating')->nullable(); // 1-5
            $table->tinyInteger('packaging_rating')->nullable(); // 1-5
            $table->decimal('tip_amount', 8, 2)->default(0);
            $table->text('feedback')->nullable();

            // Additional info
            $table->text('special_instructions')->nullable();
            $table->text('notes')->nullable();

            // Multi-tenancy
            $table->morphs('ownerable');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['order_id', 'status']);
            $table->index(['rider_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['ownerable_type']);
            $table->index(['ownerable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};

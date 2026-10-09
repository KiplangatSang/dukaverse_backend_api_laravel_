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
        Schema::create('delivery_earnings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('delivery_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_person_id')->constrained('delivery_people')->onDelete('cascade');

            // Earnings breakdown
            $table->decimal('base_fee', 8, 2)->default(0);
            $table->decimal('distance_fee', 8, 2)->default(0);
            $table->decimal('time_fee', 8, 2)->default(0);
            $table->decimal('tip_amount', 8, 2)->default(0);
            $table->decimal('bonus_amount', 8, 2)->default(0);
            $table->decimal('total_earned', 8, 2);

            // Deductions
            $table->decimal('platform_fee', 8, 2)->default(0);
            $table->decimal('tax_amount', 8, 2)->default(0);
            $table->decimal('insurance_fee', 8, 2)->default(0);
            $table->decimal('total_deductions', 8, 2)->default(0);

            // Net earnings
            $table->decimal('net_earnings', 8, 2);

            // Payment status
            $table->enum('payment_status', [
                'pending',
                'processing',
                'paid',
                'failed',
                'cancelled'
            ])->default('pending');

            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();

            // Period
            $table->date('earning_date');
            $table->string('period')->nullable(); // daily, weekly, monthly

            $table->timestamps();

            // Indexes
            $table->index(['delivery_person_id', 'earning_date']);
            $table->index(['delivery_person_id', 'payment_status']);
            $table->index(['payment_status', 'paid_at']);
            $table->index('period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_earnings');
    }
};

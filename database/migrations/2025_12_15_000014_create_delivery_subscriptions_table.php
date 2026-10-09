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
        Schema::create('delivery_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('name');
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('yearly_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('KES');

            // Features & Limits
            $table->integer('max_daily_deliveries')->default(10);
            $table->integer('max_monthly_deliveries')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(10.00); // percentage
            $table->boolean('priority_matching')->default(false);
            $table->boolean('real_time_tracking')->default(true);
            $table->boolean('customer_support')->default(false);
            $table->boolean('analytics_reports')->default(false);
            $table->boolean('custom_branding')->default(false);

            // Status
            $table->enum('status', ['active', 'inactive', 'deprecated'])->default('active');
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_subscriptions');
    }
};

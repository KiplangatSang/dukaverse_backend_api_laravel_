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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Status and lifecycle fields
            $table->enum('status', ['active', 'trial', 'cancelled', 'expired', 'suspended'])->default('active')->after('is_active');
            // expires_at already exists, skip
            $table->timestamp('cancelled_at')->nullable()->after('expires_at');
            $table->timestamp('suspended_at')->nullable()->after('cancelled_at');

            // Auto-renewal and payment fields
            // auto_renewal already exists, skip
            $table->string('payment_method')->nullable()->after('auto_renewal');
            // discounted_price already exists, skip

            // Coupon relationship
            // coupon_id already exists, skip

            // Additional metadata
            $table->json('metadata')->nullable()->after('coupon_id');
            $table->timestamp('last_payment_at')->nullable()->after('metadata');
            $table->timestamp('next_payment_at')->nullable()->after('last_payment_at');

            // Indexes for performance
            $table->index(['status', 'expires_at']);
            $table->index(['user_id', 'status']);
            $table->index('trial_end_date');
            $table->index('auto_renewal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropIndex(['status', 'expires_at']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['trial_end_date']);
            $table->dropIndex(['auto_renewal']);

            $table->dropColumn([
                'status',
                'cancelled_at',
                'suspended_at',
                'payment_method',
                'metadata',
                'last_payment_at',
                'next_payment_at',
            ]);
        });
    }
};

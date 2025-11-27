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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs("ownerable");
            $table->bigInteger("subscriptionable_id")->nullable();
            $table->string("subscriptionable_type")->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->double("paid_amount")->nullable();
            $table->string("subscription_name")->nullable();
            $table->string("subscription_description")->nullable();
            $table->string("subscription_duration")->nullable();
            $table->bigInteger("retail_id")->nullable();
            $table->bigInteger("tier_id");
            $table->string("subscription_price")->nullable();
            $table->bigInteger("subscription_level")->default(1);
            $table->string("subscription_discount")->default(0);
            $table->string("subscription_categories")->default(1);
            $table->boolean("is_renewable")->default(false);
            $table->boolean("is_active")->default(false);
            $table->boolean("auto_renewal")->default(true);
            $table->timestamp("trial_end_date")->nullable();
            $table->timestamp("expires_at")->nullable();
            $table->integer("grace_period_days")->default(7);
            $table->foreignId('coupon_id')->nullable()->references('id')->on('coupons')->onDelete('set null');
            $table->decimal('discounted_price', 10, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

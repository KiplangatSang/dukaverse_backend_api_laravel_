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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('supplierable_id')->nullable();
            $table->string('supplierable_type')->nullable();
            $table->bigInteger('user_id');
            $table->string('supplier_name');
            $table->string('account');
            $table->string('account_type');
            $table->longText('supplier_goods');
            $table->string('supplier_town');
            $table->string('supplier_constituency');
            $table->string('supplier_county');
            $table->string('supplier_country')->nullable();
            $table->string('payment_number')->nullable();
            $table->longText('supplier_profile')->nullable();
            $table->longText('supplier_documents')->nullable();
            $table->longText('supplier_relevant_documents')->nullable();
            $table->string('supplier_stock_est')->nullable();
            $table->string('is_subscribed')->nullable();
            $table->string('supplier_subscription')->nullable();
            $table->double('supplier_subscription_paid')->default(0);
            $table->string('subscription_id')->nullable();
            $table->string('paymentpreference')->nullable();
            $table->string('account_details')->nullable();
            $table->string('is_loanable')->default(false);
            $table->double('loan_limit')->default(0);
            $table->string('is_creditable')->default(false);
            $table->double('credit_limit')->default(0);
            $table->string('complete')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};

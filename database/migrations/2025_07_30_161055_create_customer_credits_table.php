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
        Schema::create('customer_credits', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('creditable_id')->nullable();
            $table->string('creditable_type')->nullable();
            $table->bigInteger('sale_transaction_id');
            $table->bigInteger('customerable_id')->nullable();
            $table->string('customerable_type')->nullable();
            $table->string('credit_id')->nullable();
            $table->double('amount');
            $table->double('amount_paid')->nullable();
            $table->boolean('pay_status')->default(false);
            $table->boolean('is_active')->default(true);
            $table->date('due_date')->nullable();
            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_credits');
    }
};

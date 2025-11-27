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
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->longText('application_id');
            $table->bigInteger('loanapplicable_id')->nullable();
            $table->string('loanapplicable_type')->nullable();
            $table->bigInteger('loans_id');
            $table->bigInteger('users_id')->nullable();
            $table->double('loan_amount');
            $table->double('disbursed_amount')->nullable()->default(0);
            $table->double('repay_amount')->nullable();
            $table->integer('loan_duration')->nullable();
            $table->double('loan_discount')->default(0);
            $table->double('loan_repaid_amount')->default(0);
            $table->integer('loan_status')->default(0);
            $table->boolean('repayment_status')->default(false);
            $table->string('loan_assigned_at')->nullable();
            $table->string('loan_assigned_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};

<?php

use App\Models\SaleTransaction;
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
        Schema::create('sale_transactions', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('transactionable_id')->nullable();
            $table->text('transactionable_type')->nullable();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->longText('transaction_id');
            $table->double('transaction_amount')->default(0);
            $table->double('VAT')->nullable();
            $table->double('discount')->nullable();
            $table->longText('deductions')->nullable();
            $table->double('total_amount')->default(0);
            $table->double('paid_amount')->nullable();
            $table->double('balance')->nullable();
            $table->boolean('pay_status')->default(false);
            $table->enum('payment_stage', SaleTransaction::PAYSTATUS)->default(SaleTransaction::PAYSTATUS[0]);
            $table->enum('sale_type', SaleTransaction::SALES_TYPES)->default(SaleTransaction::SALES_TYPES[0])->nullable();
            $table->enum('payment_type', SaleTransaction::PAYMENT_TYPE)->default(SaleTransaction::PAYMENT_TYPE[0]);
            $table->enum('transaction_status', SaleTransaction::TRANSACTION_STATUS)->default(SaleTransaction::TRANSACTION_STATUS[0]);
            $table->bigInteger('customer_id')->nullable();
            $table->bigInteger('employee_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_transactions');
    }
};

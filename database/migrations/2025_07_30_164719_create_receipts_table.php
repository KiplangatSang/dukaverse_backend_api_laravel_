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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger("receiptable_id")->nullable();
            $table->string("receiptable_type")->nullable();
            $table->foreignId("user_id")->references('id')->on("users")->onDelete("cascade");
            $table->foreignId("customer_id")->nullable()->references('id')->on("customers")->onDelete("cascade");
            $table->foreignId("sale_transaction_id")->references('id')->on("sale_transactions")->onDelete("cascade");
            $table->foreignId("transaction_id")->references('id')->on("transactions")->onDelete("cascade");
            $table->foreignId("invoice_id")->nullable()->references('id')->on("invoices");
            $table->string("type");
            $table->string("receipt_number")->unique();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};

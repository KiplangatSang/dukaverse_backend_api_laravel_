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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger("invoiceable_id")->nullable();
            $table->string("invoiceable_type")->nullable();
            $table->foreignId('customer_id')->nullable()->references('id')->on("customers")->onDelete("cascade");
            $table->foreignId('user_id')->references('id')->on("users")->onDelete("cascade");
            $table->foreignId('transaction_id')->references('id')->on("transactions")->onDelete("cascade");
            $table->foreignId('sale_transaction_id')->references('id')->on("sale_transactions")->onDelete("cascade");
            $table->string("type");
            $table->string("invoice_number")->unique();
            $table->double("invoice_amount")->default(0);
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->boolean("is_active")->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

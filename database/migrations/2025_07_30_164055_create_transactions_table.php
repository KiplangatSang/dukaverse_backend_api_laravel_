<?php

use App\Models\Transaction;
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
        /**
         * transaction_id = string used to identify transaction
         * amount = amount involved in the transaction
         * gateway = channel used to fulfil the transaction
         * accounts_id = index used to get the user account making the transaction
         * party_A = the party account details used to make  transaction ie mpesa it will be a phone number or till
         * party_B= the party account details used to receive the  transaction ie mpesa it will be a phone number or till
         * transaction_type= 1=>internal within dukaverse accounts paying for Dukaverse Products ie loans payment
         *                   2=>internal within the dukaverse accounts but not paying for dukaverse products ie deposit,transfer
         *                   3=>external and the money is not involded with dukaverse ie cash payments(just to help keep track)
         * message = message tied up with sending the transaction
         *cost = cost involved in the transaction( internal only to dukaverse. outside expenses are not accounted for)
         *currency = currency involved in the transaction ie ksh
         *purpose = purpose of the transaction ie SALES, LOANS
         *total_amount total amount involved in the transaction ie cost summed to amount sent
         *status =  status of transaction if true=> success false=> failed
         */
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->string("transaction_id");
            $table->integer("transactionable_id")->nullable();
            $table->string("transactionable_type")->nullable();
            $table->integer("sendable_id")->nullable();
            $table->string("sendable_type")->nullable();
            $table->integer("receiveable_id")->nullable();
            $table->string("receiveable_type")->nullable();
            $table->integer("purposeable_id")->nullable();
            $table->string("purposeable_type")->nullable();
            $table->string("amount");
            $table->double("cost")->default(0);
            $table->double("total_amount");
            $table->double("balance")->nullable();
            $table->string("gateway");
            $table->bigInteger("sender_accounts_id")->nullable();
            $table->text("receiver_accounts_id")->nullable();
            $table->text("sender_phone_number")->nullable();
            $table->string("receiver_phone_number")->nullable();
            $table->enum("transaction_type", Transaction::TRASACTION_TYPES)->default(Transaction::TRASACTION_TYPES[0]);
            $table->longText("message");
            $table->string("currency")->default("ksh");
            $table->string("purpose");
            $table->boolean("status")->default(false);
            $table->enum("transaction_status", Transaction::TRANSACTION_STATUS)->default(Transaction::TRANSACTION_STATUS[0]);
            $table->longText("transaction_response")->nullable();
            $table->longText("transaction_meta")->nullable();
            $table->longText("description")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

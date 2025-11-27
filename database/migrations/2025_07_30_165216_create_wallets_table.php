<?php

use App\Models\Wallet;
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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->morphs('ownerable');
            $table->bigInteger('walletable_id')->nullable();
            $table->string('walletable_type')->nullable();
            $table->string('name');
            $table->double('balance');
            $table->double('pending_balance')->nullable();
            $table->double("frozen_balance")->nullable();
            $table->boolean("is_temporary")->default(false);
            $table->boolean("can_transact")->default(true);
            $table->enum("status", Wallet::WALLET_STATUS)->default(Wallet::WALLET_STATUS[0]);
            $table->foreignId("user_id")->references('id')->on('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};

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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->integer("accountable_id")->nullable();
            $table->string("accountable_type")->nullable();
            $table->longText("account");
            $table->longText("account_ref");
            $table->double("balance")->default(0);
            $table->string("last_balance")->nullable();
            $table->string("on_hold")->nullable();;
            $table->string("initial_deposit")->nullable();;
            $table->double("max_amount")->nullable();
            $table->double("min_amount")->nullable();;
            $table->string("account_status")->default(true);
            $table->string("is_active")->default(false);
            $table->string("account_type")->default("business");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};

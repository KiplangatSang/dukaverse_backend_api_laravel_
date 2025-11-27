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
        //
        Schema::create('subscribeables', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger("subscribable_id")->nullable();
            $table->string("subscribable_type")->nullable();
            $table->foreignId('subscription_id')->constrained();
            $table->dateTime("next_billing_date");
            $table->dateTime("previous_billing_date");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('subscribables');

    }
};

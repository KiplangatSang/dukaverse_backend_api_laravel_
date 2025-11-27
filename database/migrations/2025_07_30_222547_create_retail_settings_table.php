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
        Schema::create('retail_settings', function (Blueprint $table) {
            $table->id();
            $table->morphs('ownerable');
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('retail_id')->references('id')->on('retails')->onDelete('cascade');
            $table->boolean("allow_discounts")->default(true);
            $table->boolean("allow_payments")->default(true);
            $table->boolean("show_all_products")->default(true);
            $table->boolean("adjust_for_VAT")->default(true);
            $table->double("VAT_Percentage")->default(16);
            $table->string("currency")->default("ksh");
            $table->double("required_when_below")->nullable()->default(10);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retail_settings');
    }
};

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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->unsignedBigInteger('billable_id')->nullable();
            $table->string('billable_type')->nullable();
            $table->string('bill_name');
            $table->longText('bill_description');
            $table->string('bill_amount');
            $table->string('bill_payment_duration')->nullable();
            $table->string('bill_payment_status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};

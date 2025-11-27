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
    {Schema::create('loans', function (Blueprint $table) {
        $table->id();
        $table->morphs("ownerable");
        $table->string('loan_id');
        $table->string('loanable_id')->nullable();
        $table->string('loanable_type')->nullable();
        $table->string('loan_type');
        $table->string('loan_name');
        $table->double('loan_interest_rate');
        $table->double('min_loan_range');
        $table->double('max_loan_range');
        $table->longText('loan_description');
        $table->longText('loan_regulation');
        $table->softDeletes();
            $table->timestamps();
    });}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};

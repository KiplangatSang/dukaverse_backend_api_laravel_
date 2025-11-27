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
    {Schema::create('retail_owners', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('retail_owners_id');
        $table->bigInteger('retails_id');
        $table->string('retailOwnerName');
        $table->bigInteger('users_id');
        $table->string('national_id')->nullable();
        $table->string('kra')->nullable();
        $table->string('alternate_phone_no')->nullable();
        $table->softDeletes();
            $table->timestamps();
    });}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retail_owners');
    }
};

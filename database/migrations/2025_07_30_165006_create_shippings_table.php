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

        Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->bigInteger('order_id')->nullable();
            $table->string('town')->nullable();
            $table->string('country')->nullable();
            $table->string('pincode')->nullable();
            $table->double('cost')->nullable();
            $table->string('phone_number');
            $table->string('phone_number_2')->nullable();
            $table->morphs('owner');
            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shippings');
    }
};

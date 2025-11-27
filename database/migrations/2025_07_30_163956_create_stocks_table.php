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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->string('code');
            $table->bigInteger('stockable_id')->nullable();
            $table->string('stockable_type')->nullable();
            $table->bigInteger('retail_item_id');
            $table->double('buying_price');
            $table->double('selling_price');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};

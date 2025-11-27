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
        Schema::create('retail_items', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('itemable_id')->nullable();
            $table->string('itemable_type')->nullable();
            $table->string('code');
            $table->string('name');
            $table->string('brand');
            $table->string('size');
            $table->longText('image');
            $table->double('selling_price');
            $table->unsignedBigInteger('buying_price');
            $table->unsignedBigInteger('discount')->nullable();
            $table->unsignedBigInteger('pay_price')->nullable();
            $table->longText('description')->nullable();
            $table->longText('regulation')->nullable();
            $table->string('suppliers_id')->nullable();
            $table->longText('miscellaneous')->nullable();
            $table->boolean('is_required')->nullable()->default(false);
            $table->longText('product_colors')->nullable();
            $table->longText('product_sizes')->nullable();
            $table->longText('product_images')->nullable();
            $table->double('required_when_below')->default(10)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retail_items');
    }
};

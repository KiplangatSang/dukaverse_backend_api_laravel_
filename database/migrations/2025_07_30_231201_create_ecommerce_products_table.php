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
        Schema::create('ecommerce_products', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('retail_id')->references('id')->on('retails')->onDelete('cascade');
            $table->foreignId('retail_item_id')->references('id')->on('retail_items')->onDelete('cascade');
            $table->boolean('is_visible');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_products');
    }
};

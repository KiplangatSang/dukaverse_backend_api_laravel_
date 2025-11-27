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
        Schema::create('required_items', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('requiredable_id')->nullable();
            $table->string('requiredable_type')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->bigInteger('retail_item_id')->nullable();
            $table->unsignedBigInteger('required_amount')->nullable();
            $table->unsignedBigInteger('ordered_amount')->nullable();
            $table->double('projected_cost');
            $table->boolean('is_ordered')->default(false);
            $table->bigInteger('order_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('required_items');
    }
};

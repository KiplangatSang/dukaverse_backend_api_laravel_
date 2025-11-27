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
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->string('supply_id');
            $table->integer('supplier_id');
            $table->unsignedBigInteger('supplyable_id')->nullable();
            $table->string('supplyable_type')->nullable();
            $table->longText('supply_items');
            $table->integer('pay_status');
            $table->integer('payment_balance');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};

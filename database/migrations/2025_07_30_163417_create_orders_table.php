<?php

use App\Models\Order;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->string('orderId');
            $table->longText('ordered_items');
            $table->bigInteger('items_count');
            $table->foreignId("order_location_id")->nullable()->references('id')->on('locations');
            $table->foreignId("delivery_location_id")->nullable()->references('id')->on('locations');
            $table->double('projected_cost');
            $table->double('items_cost')->default(0);
            $table->double('actual_cost')->nullable();
            $table->double('shipping_charge')->nullable();
            $table->double('tax')->nullable();
            $table->double('total_cost')->nullable();
            $table->enum('order_status', Order::ORDERSTATUS);
            $table->bigInteger('order_to')->nullable();
            $table->enum('pay_status', Order::PAYSTATUS);
            $table->bigInteger('payment_id')->default(false);
            $table->double('paid_amount')->default(false);
            $table->boolean('delivery_status')->default(false);
            $table->bigInteger('shipping_method_id')->nullable();
            $table->bigInteger('supplierable_id')->nullable();
            $table->string('supplierable_type')->nullable();
            $table->bigInteger('user_id')->nullable()->references('id')->on("users");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

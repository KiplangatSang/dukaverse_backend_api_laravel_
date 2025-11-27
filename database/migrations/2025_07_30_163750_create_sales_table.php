<?php

use App\Models\Sale;
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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('saleable_id')->nullable();
            $table->string('saleable_type')->nullable();
            $table->bigInteger('user_id');
            $table->string('code');
            $table->boolean('on_credit')->default(false);
            $table->double('selling_price')->nullable();
            $table->double('discount')->nullable();
            $table->double('tax')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->bigInteger('retail_item_id');
            $table->bigInteger('sale_transaction_id')->nullable();
            $table->string('sale_type')->nullable();
            $table->enum('payment_status', Sale::PAYSTATUS)->default(Sale::PAYSTATUS[0])->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

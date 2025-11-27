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
        Schema::create('ecommerce_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('ecommerce_id')->references('id')->on('ecommerces')->onDelete('cascade');
            $table->longText("ecommerce_sections")->nullable();
            $table->longText("ecommerce_colors")->nullable();
            $table->longText("ecommerce_promotional_messages")->nullable();
            $table->boolean("allow_discounts")->default(true);
            $table->boolean("allow_payments")->default(true);
            $table->boolean("is_age_restricted")->default(true);
            $table->boolean("connect_all_retails")->default(true);
            $table->boolean("show_all_products")->default(true);
            $table->boolean("show_support_contact")->default(true);
            $table->boolean("support_contact")->default(true);
            $table->boolean("remove_products_in_low_stock")->default(true);
            $table->double("remove_products_when_below")->nullable()->default(10);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_settings');
    }
};

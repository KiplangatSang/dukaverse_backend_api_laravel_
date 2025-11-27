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
        Schema::create('ecommerces', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('platform_id')->nullable()->references('id')->on('platforms')->onDelete('cascade');
            $table->string('name');
            $table->string('site_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone_1');
            $table->string('phone_2')->nullable();
            $table->string('country');
            $table->string('city');
            $table->string('town');
            $table->string('road_or_street');
            $table->string('building');
            $table->string('postal_code');
            $table->string('address');
            $table->string('vendor_id')->nullable();
            $table->string('vendor_password')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_subscribed')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerces');
    }
};

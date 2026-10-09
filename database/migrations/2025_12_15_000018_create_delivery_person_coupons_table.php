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
        Schema::create('delivery_person_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_person_id')->constrained('delivery_people')->onDelete('cascade');
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');

            // Usage tracking
            $table->integer('usage_count')->default(0);
            $table->integer('max_usage')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Unique constraint
            $table->unique(['delivery_person_id', 'coupon_id']);

            // Indexes
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_person_coupons');
    }
};

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
        Schema::create('tier_items', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->foreignId('tier_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('amount');
            $table->string('price');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tier_items');
    }
};

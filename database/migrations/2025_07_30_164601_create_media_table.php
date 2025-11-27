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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('mediumable_id')->nullable;
            $table->string('mediumable_type')->nullable;
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('url');
            $table->string('type');
            $table->string('name');
            $table->string('size')->nullable();
            $table->string('resolution')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};

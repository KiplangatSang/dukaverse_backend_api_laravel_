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
        Schema::create('video_call_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('video_call_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['host', 'participant', 'moderator'])->default('participant');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_video_on')->default(true);
            $table->timestamps();

            $table->foreign('video_call_id')->references('id')->on('video_calls')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['video_call_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_call_participants');
    }
};

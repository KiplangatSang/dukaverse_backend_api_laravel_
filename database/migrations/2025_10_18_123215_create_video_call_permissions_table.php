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
        Schema::create('video_call_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->boolean('can_initiate')->default(false);
            $table->boolean('can_moderate')->default(false);
            $table->boolean('can_record')->default(false);
            $table->boolean('can_share_screen')->default(false);
            $table->boolean('can_mute_others')->default(false);
            $table->boolean('can_kick_participants')->default(false);
            $table->boolean('can_send_messages')->default(true);
            $table->boolean('can_upload_files')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->unique(['user_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_call_permissions');
    }
};

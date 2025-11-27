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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->morphs("messageable");
            $table->bigInteger('replyable_id')->nullable();
            $table->string('replyable_type')->nullable();
            $table->bigInteger("admin_id")->nullable();
            $table->bigInteger('sender_id');
            $table->bigInteger('parent_id')->nullable();
            $table->morphs("sendable");
            $table->string("title")->nullable();
            $table->longText("message")->nullable();
            $table->longText("replies")->nullable();;
            $table->boolean("can_reply")->default(true);
            $table->boolean("reply_status")->nullable()->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};

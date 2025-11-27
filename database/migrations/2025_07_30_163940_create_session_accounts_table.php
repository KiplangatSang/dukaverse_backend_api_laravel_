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
        Schema::create('session_accounts', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger("sessionable_id")->nullable();
            $table->string("sessionable_type")->nullable();
            $table->foreignId("user_id")->references('id')->on('users')->onDelete('cascade');
            $table->string('token', 64)->unique()->nullable();
            $table->longText('session_app_url')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->dateTime('last_seen')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_accounts');
    }
};

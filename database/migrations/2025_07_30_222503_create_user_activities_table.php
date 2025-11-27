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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->morphs('ownerable');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->string('session_id')->nullable();
            $table->bigInteger('activityable_id')->nullable();
            $table->string('activityable_type')->nullable();
            $table->string('activity_type');
            $table->string('page')->nullable();
            $table->string('operating_system')->nullable();
            $table->string('ip_addresses')->nullable();
            $table->json('meta_data')->nullable();
            $table->string('is_success')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};

<?php

use App\Models\Campaign;
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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->morphs('ownerable');
            $table->bigInteger('campaignable_id')->nullable();
            $table->string('campaignable_type')->nullable();
            $table->foreignId("user_id")->nullable()->references('id')->on('users');
            $table->string('name');
            $table->string('colors')->default(json_encode(Campaign::DEFAULT_COLORS));
            $table->string('description')->nullable();
            $table->string('avatar')->nullable();
            $table->string('link')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('due_date');
            $table->string('target')->nullable();
            $table->string('budget')->nullable();
            $table->enum('status', Campaign::CAMPAIGN_STATUS)->default(Campaign::CAMPAIGN_STATUS[0]);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};

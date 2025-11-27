<?php

use App\Models\Lead;
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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->morphs('ownerable');
            $table->bigInteger('leadable_id')->nullable();
            $table->string('leadable_type')->nullable();
            $table->foreignId("campaign_id")->nullable()->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreignId("user_id")->nullable()->references('id')->on('users');
            $table->string("name");
            $table->string("email")->nullable();
            $table->string("phone_number")->nullable();
            $table->string("profile_url")->default(Lead::NO_PROFILE);
            $table->boolean("is_contacted")->default(false);
            $table->boolean("is_replied")->default(false);
            $table->enum("status", Lead::LEAD_STATUS)->default(Lead::LEAD_STATUS[0]);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};

<?php

use App\Repositories\AppRepository;
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
        Schema::create('profiles', function (Blueprint $table) {
            $apprepo   = new AppRepository();
            $noprofile = $apprepo->getBaseImages()['noprofile'];
            $table->id();
            $table->bigInteger("profileable_id");
            $table->string("profileable_type");
            $table->bigInteger("user_id")->nullable();
            $table->bigInteger("retail_id")->nullable();
            $table->string('full_name')->nullable();
            $table->longText('profile_image');
            $table->string('national_id')->nullable();
            $table->string('relevant_documents')->nullable();
            $table->string('kra')->nullable();
            $table->string('alternate_phone_no')->nullable();
            $table->string('address')->nullable();
            $table->string('country')->nullable();
            $table->string('country_code')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('sub_county')->nullable();
            $table->string('town')->nullable();
            $table->string('street')->nullable();
            $table->boolean('uses_google_maps')->nullable();
            $table->string('ip_address')->nullable();
            $table->longText('facebook_profile')->nullable();
            $table->longText('instagram_profile')->nullable();
            $table->longText('twitter_handle')->nullable();
            $table->longText('linkedIn_profile')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};

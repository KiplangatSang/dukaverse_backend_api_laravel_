<?php

use App\Models\Tier;
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
        Schema::create('tiers', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('tierable_id')->nullable();
            $table->string('tierable_type')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('type')->nullable()->default(Tier::RETAIL_TIERS);
            $table->double('price');
            $table->longText('benefits')->nullable();
            $table->enum('billing_duration', Tier::BILLINGDURATIONS);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_recommended')->default(false);
            $table->integer('trial_period_days')->default(0); // 0 means no trial
            $table->decimal('trial_price', 10, 2)->default(0);
            $table->integer('max_trial_extensions')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiers');
    }
};

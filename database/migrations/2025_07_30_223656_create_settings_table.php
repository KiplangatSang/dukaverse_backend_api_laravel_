<?php

use App\Models\Setting;
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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('settingable_id')->nullable();
            $table->string('settingable_type')->nullable();
            $table->boolean('email_notifications')->default(true);
            $table->boolean('notify_on_account_changes')->default(true);
            $table->boolean('notify_on_new_products')->default(true);
            $table->boolean('notify_on_promotions_and_offers')->default(true);
            $table->boolean('notify_on_security_alerts')->default(true);
            $table->enum('theme', Setting::THEMES)->default(Setting::THEMES[0]);
            $table->boolean('send_personal_sales_made')->default(true);
            $table->longText('notification_schedules')->nullable();
            $table->text('timezone')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

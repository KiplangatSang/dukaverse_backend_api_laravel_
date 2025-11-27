<?php

use App\Models\User;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('phone_number')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('status', User::USER_ACCOUNT_STATUS)->default(USer::USER_ACCOUNT_STATUS["active"]);
            $table->boolean('terms')->default(false);
            $table->longText('requirements')->default(null)->nullable();
            $table->string('login_type')->default('direct');
            $table->string('find_out_site')->default('search');
            $table->foreignId('referal_id')->nullable()->references('id')->on('users');
            $table->dateTime('last_seen')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->enum('role', User::ROLETYPES)->default(User::ROLETYPES[User::ADMIN_ACCOUNT_TYPE]);
            // $table->enum('user_level', User::USER_LEVEL)->default(User::USER_LEVEL[User::LEVEL_1]);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();

        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

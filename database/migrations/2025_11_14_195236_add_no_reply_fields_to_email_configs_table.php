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
        Schema::table('email_configs', function (Blueprint $table) {
            $table->string('no_reply_email')->nullable();
            $table->string('no_reply_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_configs', function (Blueprint $table) {
            $table->dropColumn(['no_reply_email', 'no_reply_name']);
        });
    }
};

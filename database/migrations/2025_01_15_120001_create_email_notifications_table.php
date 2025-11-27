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
        Schema::create('email_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('email_config_id');
            $table->string('message_id')->unique(); // IMAP message ID
            $table->string('subject');
            $table->text('body');
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->json('to'); // Recipients
            $table->json('attachments')->nullable(); // List of attachment paths or data
            $table->timestamp('received_at');
            $table->boolean('processed')->default(false);
            $table->timestamps();

            $table->foreign('email_config_id')->references('id')->on('email_configs')->onDelete('cascade');
            $table->index(['email_config_id', 'processed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_notifications');
    }
};

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
        Schema::create('auto_emails', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger_event'); // e.g., 'user_registered', 'order_placed', 'payment_received'
            $table->string('subject');
            $table->text('body');
            $table->unsignedBigInteger('email_config_id');
            $table->json('conditions')->nullable(); // JSON conditions for when to send
            $table->integer('delay_minutes')->default(0); // Delay before sending
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('email_config_id')->references('id')->on('email_configs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_emails');
    }
};

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
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('verification_id')->unique();
            $table->enum('type', ['email', 'phone', 'identity']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('code')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->json('metadata')->nullable(); // Store additional verification data
            $table->boolean('is_paid')->default(false);
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'status']);
            $table->index('verification_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifications');
    }
};

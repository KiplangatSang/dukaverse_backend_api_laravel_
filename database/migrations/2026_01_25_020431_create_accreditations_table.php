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
        Schema::create('accreditations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('accreditation_id')->unique();
            $table->enum('business_type', ['individual', 'business']);
            $table->integer('years_experience');
            $table->json('specializations');
            $table->json('references');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'expired', 'renewal_pending'])->default('pending');
            $table->boolean('is_paid')->default(false);
            $table->string('business_license_path')->nullable();
            $table->string('insurance_path')->nullable();
            $table->string('background_check_path')->nullable();
            $table->string('training_certificate_path')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('renewal_id')->nullable();
            $table->timestamp('renewal_date')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accreditations');
    }
};

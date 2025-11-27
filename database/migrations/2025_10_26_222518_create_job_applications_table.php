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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->unsignedBigInteger('applicant_id');
            $table->text('cover_letter')->nullable();
            $table->string('status')->default('pending'); // pending, reviewed, shortlisted, rejected, hired
            $table->json('resume_data')->nullable(); // parsed resume data
            $table->string('resume_file')->nullable(); // file path
            $table->decimal('expected_salary', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->text('notes')->nullable(); // internal notes
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->foreign('job_id')->references('id')->on('job_postings')->onDelete('cascade');
            $table->foreign('applicant_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['job_id', 'applicant_id']); // prevent duplicate applications
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};

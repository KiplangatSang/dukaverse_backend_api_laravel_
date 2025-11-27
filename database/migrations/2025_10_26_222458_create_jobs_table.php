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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->text('requirements');
            $table->string('location')->nullable();
            $table->string('job_type')->default('full-time'); // full-time, part-time, contract, freelance
            $table->string('experience_level')->default('entry'); // entry, mid, senior, executive
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('department')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('application_deadline')->nullable();
            $table->json('benefits')->nullable(); // array of benefits
            $table->json('skills_required')->nullable(); // array of required skills
            $table->unsignedBigInteger('posted_by');
            $table->foreign('posted_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};

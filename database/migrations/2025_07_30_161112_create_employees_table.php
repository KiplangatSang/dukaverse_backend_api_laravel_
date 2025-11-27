<?php

use App\Models\Employee;
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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('user_id');
            $table->bigInteger('employeeable_id')->nullable();
            $table->string('employeeable_type')->nullable();
            $table->string('employee_national_id')->nullable();
            $table->double('employee_salary')->nullable();
            $table->string('employee_job_title')->nullable();
            $table->string('employee_job_description')->nullable();
            $table->string('employee_job_location')->nullable();
            $table->enum('employee_job_type', Employee::EMPLOYEE_TYPE)->nullable();
            $table->enum('employee_status', Employee::EMPLOYEE_STATUS)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

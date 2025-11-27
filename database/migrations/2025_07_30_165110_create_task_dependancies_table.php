<?php

use App\Models\TaskDependancy;
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
        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('taskable_id')->nullable();
            $table->string('taskable_type')->nullable();
            $table->foreignId('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreignId('task_id')->references('id')->on('tasks')->onDelete('cascade');    // Task that depends on another task
            $table->foreignId('depends_on')->references('id')->on('tasks')->onDelete('cascade'); // The task it depends on
            $table->enum('dependency_type', TaskDependancy::TASK_DEPENDENCIES)->default('FS');   // Dependency type
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_dependancies');
    }
};

<?php

use App\Models\Task;
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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->morphs('ownerable');
            $table->bigInteger("taskable_id")->nullable();
            $table->string("taskable_type")->nullable();
            $table->foreignId('user_id')->references('id')->on("users")->onDelete('cascade');
            $table->bigInteger('parent_id')->nullable();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->dateTime('date_closed')->nullable();
            $table->integer('progress')->default(0);
            $table->json('dependencies')->nullable();
            $table->enum('priority', Task::TASK_PRIORITIES)->default(Task::TASK_PRIORITIES[0]); // Task priority
            $table->enum('status', Task::TASK_STATUS)->default(Task::TASK_STATUS[0]);           // Task priority
            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

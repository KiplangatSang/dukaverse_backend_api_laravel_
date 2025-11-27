<?php

use App\Models\Project;
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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('projectable_id')->nullable();
            $table->string('projectable_type')->nullable();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name');
            $table->string('colors')->default(json_encode(Project::DEFAULT_COLORS));
            $table->text('overview')->nullable();
            $table->longText('description')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('due_date');
            $table->decimal('budget', 10, 2)->nullable();
            $table->string('avatar')->nullable();
            $table->enum('status', Project::PROJECT_STATUS)->default(Project::PROJECT_STATUS[0])->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

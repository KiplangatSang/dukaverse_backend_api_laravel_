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
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('todoable_id')->nullable();
            $table->string('todoable_type')->nullable();
            $table->string('todo');
            $table->longText('note')->nullable();
            $table->boolean('done')->default(false);
            $table->boolean('archived')->default(false);
            $table->foreignId('project_id')->nullable()->references('id')->on('projects')->onDelete('cascade');
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->references('id')->on('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};

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
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');

            $table->string('name'); // main, upstairs, outdoor, basement
            $table->string('display_name');
            $table->text('description')->nullable();

            // Layout dimensions
            $table->integer('width')->default(1000);
            $table->integer('height')->default(800);

            // Floor elements (entrance, kitchen, bar, restrooms, etc.)
            $table->json('elements')->nullable();

            // Settings
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->unique(['restaurant_id', 'name']);
            $table->index(['restaurant_id', 'is_active']);
        });

        // Add floor reference to tables
        Schema::table('tables', function (Blueprint $table) {
            $table->foreignId('floor_id')->nullable()->after('restaurant_id')->constrained()->onDelete('set null');
            $table->string('floor')->nullable()->after('section'); // Keep for backward compatibility
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign(['floor_id']);
            $table->dropColumn(['floor_id', 'floor']);
        });

        Schema::dropIfExists('floors');
    }
};

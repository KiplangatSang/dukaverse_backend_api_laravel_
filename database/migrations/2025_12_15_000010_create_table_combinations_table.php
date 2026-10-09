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
        Schema::create('table_combinations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');

            $table->string('combined_name');
            $table->integer('total_capacity');
            $table->integer('party_size');

            // Related order (polymorphic)
            $table->nullableMorphs('orderable');

            $table->enum('status', ['active', 'split'])->default('active');
            $table->text('notes')->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('split_by')->nullable()->constrained('users');
            $table->timestamp('split_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['restaurant_id', 'status']);
        });

        // Pivot table for combination tables
        Schema::create('combination_table', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_combination_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['table_combination_id', 'table_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combination_table');
        Schema::dropIfExists('table_combinations');
    }
};

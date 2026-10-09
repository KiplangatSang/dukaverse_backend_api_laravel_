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
        Schema::create('table_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');

            // Status change
            $table->string('previous_status');
            $table->string('new_status');

            // Related entities (polymorphic)
            $table->nullableMorphs('related'); // Can be order, reservation, etc.

            // Change details
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            // Tracking
            $table->foreignId('changed_by')->nullable()->constrained('users');
            $table->timestamp('changed_at');

            $table->timestamps();

            // Indexes
            $table->index(['table_id', 'changed_at']);
            $table->index(['restaurant_id', 'changed_at']);
            $table->index(['new_status', 'changed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_status_history');
    }
};

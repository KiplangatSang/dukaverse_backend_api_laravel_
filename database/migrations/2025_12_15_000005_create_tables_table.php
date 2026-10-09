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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');

            $table->string('table_number');
            $table->integer('capacity');
            $table->string('section')->default('main'); // main, patio, private, bar
            $table->json('features')->nullable(); // smoking, window, booth, etc.
            $table->enum('status', [
                'available',
                'occupied',
                'reserved',
                'cleaning',
                'maintenance',
                'out_of_order'
            ])->default('available');

            // Position for floor plan
            $table->decimal('position_x', 8, 2)->nullable();
            $table->decimal('position_y', 8, 2)->nullable();

            // Additional info
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->unique(['restaurant_id', 'table_number']);
            $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'section']);
            $table->index(['status', 'capacity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};

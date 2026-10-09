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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('menu_categories')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->string('image_url')->nullable();
            $table->json('images')->nullable();
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->integer('preparation_time')->default(15); // in minutes
            $table->integer('calories')->nullable();
            $table->json('allergens')->nullable();
            $table->json('dietary_info')->nullable();
            $table->json('tags')->nullable();
            $table->json('ingredients')->nullable();
            $table->json('nutritional_info')->nullable();
            $table->json('availability_schedule')->nullable();

            // Multi-tenancy
            $table->morphs('ownerable');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['restaurant_id', 'category_id']);
            $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'is_available']);
            $table->index(['restaurant_id', 'is_featured']);
            $table->index(['restaurant_id', 'sort_order']);
            $table->index(['category_id', 'sort_order']);
            $table->index(['price']);
            $table->index(['ownerable_type', 'ownerable_id', "name"]);
            $table->index(['name', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};

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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('address');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('inactive');

            // Multi-tenancy
            $table->morphs('ownerable');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'created_at']);
            $table->index(['ownerable_type', 'ownerable_id', "name"]);
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};

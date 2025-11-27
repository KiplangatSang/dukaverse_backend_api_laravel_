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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('roleable_id')->nullable();
            $table->string('roleable_type')->nullable();
            $table->integer('is_super_admin')->default(false);
            $table->integer('is_retail_owner')->default(false);
            $table->integer('is_employee')->default(true);
            $table->longText('role');
            $table->longText('permissions');
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

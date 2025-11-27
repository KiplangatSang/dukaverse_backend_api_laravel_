<?php

use App\Models\Terminal;
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
        Schema::create('terminals', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger("terminalable_id")->nullable();
            $table->string("terminalable_type")->nullable();
            $table->string("terminal_number")->unique();
            $table->string("terminal_serial")->unique();
            $table->string("type")->default(Terminal::SALES_TERMINAL_TYPE);
            $table->boolean("is_active")->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminals');
    }
};

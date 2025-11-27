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
        Schema::table('job_applications', function (Blueprint $table) {
            $table->foreignId('calendar_id')->nullable()->constrained('calendars')->onDelete('set null');
            $table->foreignId('task_id')->nullable()->constrained('tasks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropForeign(['calendar_id']);
            $table->dropForeign(['task_id']);
            $table->dropColumn(['calendar_id', 'task_id']);
        });
    }
};

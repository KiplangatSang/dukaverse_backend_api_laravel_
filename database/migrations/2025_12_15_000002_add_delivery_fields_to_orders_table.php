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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('priority')->default('normal')->after('status'); // normal, urgent, rush
            $table->timestamp('estimated_ready_time')->nullable()->after('scheduled_for');
            $table->json('delivery_address')->nullable()->after('scheduled_for');
            $table->decimal('delivery_fee', 8, 2)->default(0)->after('total_amount');
            $table->string('delivery_status')->nullable()->after('status'); // pending, assigned, picked_up, delivered

            $table->index(['status', 'order_type']);
            $table->index(['priority', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['priority', 'estimated_ready_time', 'delivery_address', 'delivery_fee', 'delivery_status']);
        });
    }
};

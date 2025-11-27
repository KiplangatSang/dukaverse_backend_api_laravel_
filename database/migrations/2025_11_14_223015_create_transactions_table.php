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
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->string('currency', 3)->default('USD');
                $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
                $table->enum('type', ['initial', 'renewal', 'upgrade', 'downgrade', 'refund'])->default('initial');
                $table->string('payment_method')->nullable(); // stripe, paypal, etc.
                $table->string('transaction_id')->nullable(); // external payment gateway ID
                $table->text('description')->nullable();
                $table->json('metadata')->nullable(); // additional payment data
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['subscription_id', 'type']);
                $table->index('transaction_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

<?php

use App\Repositories\AppRepository;
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
        Schema::create('offices', function (Blueprint $table) {
            $noprofile = AppRepository::noprofile;
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('officeable_id')->nullable();
            $table->string('officeable_type')->nullable();
            $table->bigInteger('user_id');
            $table->string('name');
            $table->string('name_id');
            $table->longText('industry')->nullable();
            $table->longText('goods_and_services')->nullable();
            $table->string('town')->nullable();
            $table->string('constituency')->nullable();
            $table->string('county')->nullable();
            $table->string('country')->nullable();
            $table->string('payment_number')->nullable();
            $table->text('profile')->nullable();
            $table->longText('documents')->nullable();
            $table->longText('relevant_documents')->nullable();
            $table->string('employees')->nullable();
            $table->string('is_subscribed')->nullable();
            $table->string('subscription')->nullable();
            $table->double('subscription_paid')->default(0);
            $table->string('subscription_id')->nullable();
            $table->string('payment_preference')->nullable();
            $table->string('account_details')->nullable();
            $table->string('is_loanable')->default(false);
            $table->double('loan_limit')->default(0);
            $table->string('is_creditable')->default(false);
            $table->double('credit_limit')->default(0);
            $table->double('notification_time')->default(0);
            $table->double('timezone')->default(0);
            $table->string('complete')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};

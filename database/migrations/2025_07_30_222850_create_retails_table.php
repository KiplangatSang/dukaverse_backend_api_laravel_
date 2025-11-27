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
        Schema::create('retails', function (Blueprint $table) {
            $apprepo   = new AppRepository();
            $noprofile = $apprepo->getBaseImages()['noprofile'];
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('retailable_id')->nullable();
            $table->string('retailable_type')->nullable();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->string('retail_name');
            $table->string('name')->nullable();
            $table->string('retail_name_id');
            $table->longText('retail_goods');
            $table->string('retail_town')->nullable();
            $table->string('retail_constituency')->nullable();
            $table->string('retail_county')->nullable();
            $table->string('retail_country')->nullable();
            $table->string('payment_number')->nullable();
            $table->longText('retail_profile')->nullable();
            $table->longText('retail_documents')->nullable();
            $table->longText('retail_relevant_documents')->nullable();
            $table->string('retail_emp_no')->nullable();
            $table->string('retail_stock_est')->nullable();
            $table->string('is_subscribed')->nullable();
            $table->string('retail_subscription')->nullable();
            $table->double('retail_subscription_paid')->default(0);
            $table->string('subscription_id')->nullable();
            $table->string('paymentpreference')->nullable();
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
        Schema::dropIfExists('retails');
    }
};

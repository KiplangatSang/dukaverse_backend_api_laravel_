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
        Schema::create('customers', function (Blueprint $table) {
            $apprepo   = new AppRepository();
            $noprofile = $apprepo->getBaseImages()['noprofile'];

            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger('customerable_id')->nullable();
            $table->string('customerable_type')->nullable();
            $table->string('name');
            $table->string('id_number')->nullable();
            $table->string('phone_number');
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->bigInteger('sale_transaction_id')->nullable();
            $table->string('profile')->default($noprofile);
            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

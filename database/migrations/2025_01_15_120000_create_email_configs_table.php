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
        Schema::create('email_configs', function (Blueprint $table) {
            $table->id();
            $table->string('client_name')->unique(); // To identify different clients
            $table->string('imap_host');
            $table->integer('imap_port')->default(993);
            $table->string('imap_encryption')->default('ssl'); // ssl, tls, or null
            $table->string('imap_username');
            $table->string('imap_password');
            $table->string('smtp_host');
            $table->integer('smtp_port')->default(587);
            $table->string('smtp_encryption')->default('tls'); // tls, ssl, or null
            $table->string('smtp_username');
            $table->string('smtp_password');
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_configs');
    }
};

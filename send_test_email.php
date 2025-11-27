<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;

Mail::raw('This is a test email from Dukaverse.', function($message) {
    $message->to('ceo@ceroisoft.com')->subject('Test Email from Dukaverse');
});

echo "Test email sent to ceo@ceroisoft.com\n";

<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\EmailConfig;
use App\Services\EmailService;

// Find or create EmailConfig
$config = EmailConfig::where('client_name', 'Ceroisoft')->first();

if (!$config) {
    $config = EmailConfig::create([
        'client_name' => 'Ceroisoft',
        'imap_host' => 'mail.crandtel.com',
        'imap_port' => 993,
        'imap_encryption' => 'ssl',
        'imap_username' => 'info@crandtel.com',
        'imap_password' => 'CeroisoftInfo1998@',
        'smtp_host' => 'mail.crandtel.com',
        'smtp_port' => 465,
        'smtp_encryption' => 'ssl',
        'smtp_username' => 'info@crandtel.com',
        'smtp_password' => 'CeroisoftInfo1998@',
        'from_email' => 'info@crandtel.com',
        'from_name' => 'Dukaverse',
        'active' => true,
    ]);
    echo "EmailConfig created with ID: " . $config->id . "\n";
} else {
    echo "EmailConfig found with ID: " . $config->id . "\n";
}

// Send email
$emailService = app(EmailService::class);
$data = [
    'to' => ['ceo@ceroisoft.com'],
    'subject' => 'Test Dynamic Email',
    'body' => 'This is a test email sent using dynamic email config.',
];

echo "Data prepared.\n";

$emailService = app(EmailService::class);

echo "Service loaded.\n";

try {
    $emailService->sendEmail($data, $config);
    echo "Email sent.\n";
} catch (\Exception $e) {
    echo "Send email failed: " . $e->getMessage() . "\n";
}

// Read emails
try {
    $emailService->readEmails($config);
    echo "Emails checked.\n";
} catch (\Exception $e) {
    echo "Read emails failed: " . $e->getMessage() . "\n";
}

echo "Test completed.\n";

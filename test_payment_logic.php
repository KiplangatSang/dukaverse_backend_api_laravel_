<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Verification;
use App\Models\Accreditation;
use App\Services\DeliveryApp\VerificationService;
use App\Services\DeliveryApp\AccreditationService;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "Testing Verification and Accreditation Payment Logic\n";
echo "==================================================\n\n";

// Create a test user
$user = User::first() ?? User::factory()->create();

echo "Test User ID: " . $user->id . "\n\n";

$verificationService = new VerificationService();
$accreditationService = new AccreditationService();

// Test 1: Check verification status for unpaid user
echo "Test 1: Verification status for unpaid user\n";
$result = $verificationService->getStatus($user);
echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . $result['message'] . "\n";
echo "HTTP Code: " . $result['httpCode'] . "\n";
if (isset($result['data']['is_paid'])) {
    echo "Is Paid: " . ($result['data']['is_paid'] ? 'YES' : 'NO') . "\n";
}
echo "\n";

// Test 2: Check accreditation status for unpaid user
echo "Test 2: Accreditation status for unpaid user\n";
$result = $accreditationService->getStatus($user);
echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . $result['message'] . "\n";
echo "HTTP Code: " . $result['httpCode'] . "\n";
if (isset($result['data']['is_paid'])) {
    echo "Is Paid: " . ($result['data']['is_paid'] ? 'YES' : 'NO') . "\n";
}
echo "\n";

// Test 3: Try to initiate verification without payment
echo "Test 3: Initiate verification without payment\n";
$result = $verificationService->initiate($user, 'email');
echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . $result['message'] . "\n";
echo "HTTP Code: " . $result['httpCode'] . "\n";
echo "\n";

// Test 4: Try to apply for accreditation without payment
echo "Test 4: Apply for accreditation without payment\n";
$result = $accreditationService->apply($user, [
    'business_type' => 'individual',
    'years_experience' => 5,
    'specializations' => ['delivery'],
    'references' => []
]);
echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . $result['message'] . "\n";
echo "HTTP Code: " . $result['httpCode'] . "\n";
echo "\n";

// Test 5: Create payment transaction for verification
echo "Test 5: Create verification payment transaction\n";
$result = $verificationService->createPaymentTransaction($user);
echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
if ($result['success']) {
    echo "Transaction ID: " . $result['data']['transaction_id'] . "\n";
    echo "Amount: " . $result['data']['amount'] . "\n";
    echo "Status: " . $result['data']['status'] . "\n";
} else {
    echo "Message: " . $result['message'] . "\n";
}
echo "\n";

// Test 6: Create payment transaction for accreditation
echo "Test 6: Create accreditation payment transaction\n";
$result = $accreditationService->createPaymentTransaction($user);
echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
if ($result['success']) {
    echo "Transaction ID: " . $result['data']['transaction_id'] . "\n";
    echo "Amount: " . $result['data']['amount'] . "\n";
    echo "Status: " . $result['data']['status'] . "\n";
} else {
    echo "Message: " . $result['message'] . "\n";
}
echo "\n";

echo "Testing completed!\n";

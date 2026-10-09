<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Verification;
use App\Services\DeliveryApp\VerificationService;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "Testing KYC Verification Implementation\n";
echo "======================================\n\n";

// Create a test user
$user = User::first() ?? User::factory()->create();

echo "Test User ID: " . $user->id . "\n\n";

$verificationService = new VerificationService();

// Test 1: Create payment transaction for identity verification
echo "Test 1: Create identity verification payment transaction\n";
$result = $verificationService->createPaymentTransaction($user);
echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
if ($result['success']) {
    echo "Transaction ID: " . $result['data']['transaction_id'] . "\n";
    echo "Amount: " . $result['data']['amount'] . "\n";
    echo "Status: " . $result['data']['status'] . "\n";
    $transactionId = $result['data']['transaction_id'];
} else {
    echo "Message: " . $result['message'] . "\n";
}
echo "\n";

// Test 2: Try to initiate identity verification without payment
echo "Test 2: Initiate identity verification without payment\n";
$result = $verificationService->initiate($user, 'identity');
echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . $result['message'] . "\n";
echo "HTTP Code: " . $result['httpCode'] . "\n";
echo "\n";

// Test 3: Mark payment as completed
echo "Test 3: Mark payment as completed\n";
if (isset($transactionId)) {
    $result = $verificationService->completePaymentTransaction($user, $transactionId);
    echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    if ($result['success']) {
        echo "Verification marked as paid\n";
    } else {
        echo "Message: " . $result['message'] . "\n";
    }
} else {
    echo "No transaction ID available\n";
}
echo "\n";

// Test 4: Now initiate identity verification (should work)
echo "Test 4: Initiate identity verification after payment\n";
$result = $verificationService->initiate($user, 'identity');
echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
if ($result['success']) {
    echo "Verification ID: " . $result['data']['verification_id'] . "\n";
    $verificationId = $result['data']['verification_id'];
} else {
    echo "Message: " . $result['message'] . "\n";
    echo "HTTP Code: " . $result['httpCode'] . "\n";
}
echo "\n";

// Test 5: Submit KYC data
echo "Test 5: Submit KYC data\n";
if (isset($verificationId)) {
    $kycData = [
        'id_number' => '123456789',
        'id_type' => 'national_id',
        'full_name' => 'John Doe',
        'date_of_birth' => '1990-01-01',
        'nationality' => 'Kenyan',
        'address' => '123 Main St, Nairobi, Kenya'
    ];
    $result = $verificationService->submitKYC($user, $kycData);
    echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    if ($result['success']) {
        echo "KYC data submitted successfully\n";
    } else {
        echo "Message: " . $result['message'] . "\n";
    }
} else {
    echo "No verification ID available\n";
}
echo "\n";

// Test 6: Upload selfie with ID (simulated)
echo "Test 6: Upload selfie with ID document\n";
if (isset($verificationId)) {
    // Create a dummy file for testing
    $dummyFile = tmpfile();
    fwrite($dummyFile, 'dummy image content');
    fseek($dummyFile, 0);

    $file = new \Illuminate\Http\UploadedFile(
        stream_get_meta_data($dummyFile)['uri'],
        'selfie.jpg',
        'image/jpeg',
        null,
        true
    );

    $result = $verificationService->uploadKYCDocument($user, $file, 'selfie_with_id');
    echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    if ($result['success']) {
        echo "Selfie uploaded successfully\n";
    } else {
        echo "Message: " . $result['message'] . "\n";
    }

    fclose($dummyFile);
} else {
    echo "No verification ID available\n";
}
echo "\n";

// Test 7: Upload ID document (simulated)
echo "Test 7: Upload ID document\n";
if (isset($verificationId)) {
    // Create a dummy file for testing
    $dummyFile = tmpfile();
    fwrite($dummyFile, 'dummy image content');
    fseek($dummyFile, 0);

    $file = new \Illuminate\Http\UploadedFile(
        stream_get_meta_data($dummyFile)['uri'],
        'id.jpg',
        'image/jpeg',
        null,
        true
    );

    $result = $verificationService->uploadKYCDocument($user, $file, 'id_document');
    echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    if ($result['success']) {
        echo "ID document uploaded successfully\n";
    } else {
        echo "Message: " . $result['message'] . "\n";
    }

    fclose($dummyFile);
} else {
    echo "No verification ID available\n";
}
echo "\n";

// Test 8: Check verification status after KYC submission
echo "Test 8: Check verification status after KYC submission\n";
$result = $verificationService->getStatus($user);
echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
if ($result['success'] && isset($result['data']['verifications']['identity'])) {
    $identityStatus = $result['data']['verifications']['identity'];
    echo "Identity Verification Status:\n";
    echo "- Verified: " . ($identityStatus['verified'] ? 'YES' : 'NO') . "\n";
    echo "- Paid: " . ($identityStatus['is_paid'] ? 'YES' : 'NO') . "\n";
    echo "- KYC Submitted: " . ($identityStatus['kyc_submitted'] ? 'YES' : 'NO') . "\n";
    echo "- KYC Verified: " . ($identityStatus['kyc_verified'] ? 'YES' : 'NO') . "\n";
} else {
    echo "Could not retrieve identity verification status\n";
}
echo "\n";

// Test 9: Verify KYC (admin function)
echo "Test 9: Verify KYC (admin function)\n";
if (isset($verificationId)) {
    $result = $verificationService->verifyKYC($user, $verificationId, true, 'KYC verification completed successfully');
    echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    if ($result['success']) {
        echo "KYC verification completed\n";
    } else {
        echo "Message: " . $result['message'] . "\n";
    }
} else {
    echo "No verification ID available\n";
}
echo "\n";

// Test 10: Final status check
echo "Test 10: Final verification status check\n";
$result = $verificationService->getStatus($user);
echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
if ($result['success'] && isset($result['data']['verifications']['identity'])) {
    $identityStatus = $result['data']['verifications']['identity'];
    echo "Final Identity Verification Status:\n";
    echo "- Verified: " . ($identityStatus['verified'] ? 'YES' : 'NO') . "\n";
    echo "- Paid: " . ($identityStatus['is_paid'] ? 'YES' : 'NO') . "\n";
    echo "- KYC Submitted: " . ($identityStatus['kyc_submitted'] ? 'YES' : 'NO') . "\n";
    echo "- KYC Verified: " . ($identityStatus['kyc_verified'] ? 'YES' : 'NO') . "\n";
    if (isset($identityStatus['verified_at'])) {
        echo "- Verified At: " . $identityStatus['verified_at'] . "\n";
    }
    if (isset($identityStatus['kyc_verified_at'])) {
        echo "- KYC Verified At: " . $identityStatus['kyc_verified_at'] . "\n";
    }
} else {
    echo "Could not retrieve final identity verification status\n";
}
echo "\n";

echo "KYC Verification Testing Completed!\n";

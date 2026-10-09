<?php

namespace App\Services\DeliveryApp;

use App\Models\User;
use App\Models\Verification;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VerificationService
{
    public function initiate(User $user, string $type): array
    {
        try {
            // Validate type
            if (!in_array($type, [Verification::TYPE_EMAIL, Verification::TYPE_PHONE, Verification::TYPE_IDENTITY])) {
                return [
                    'success' => false,
                    'message' => 'Invalid verification type',
                    'httpCode' => 400,
                ];
            }

            // Check if user has paid for verification service (only for identity)
            if ($type === Verification::TYPE_IDENTITY) {
                $existingVerification = Verification::where('user_id', $user->id)
                    ->where('type', $type)
                    ->first();

                if ($existingVerification && !$existingVerification->is_paid) {
                    return [
                        'success' => false,
                        'message' => 'Payment required for identity verification service. Please complete payment first.',
                        'httpCode' => 402, // Payment Required
                    ];
                }
            }

            // Check if user has paid for verification service
            $existingVerification = Verification::where('user_id', $user->id)
                ->where('type', $type)
                ->first();

            if ($existingVerification && $existingVerification->isCompleted()) {
                return [
                    'success' => false,
                    'message' => ucfirst($type) . ' is already verified',
                    'httpCode' => 400,
                ];
            }

            // Create or update verification record
            $verification = Verification::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => $type,
                ],
                [
                    'verification_id' => 'ver_' . $type . '_' . time() . '_' . $user->id,
                    'status' => Verification::STATUS_PENDING,
                    'code' => $type !== Verification::TYPE_IDENTITY ? $this->generateVerificationCode() : null,
                    'expires_at' => $type !== Verification::TYPE_IDENTITY ? now()->addMinutes(15) : null,
                ]
            );

            return [
                'success' => true,
                'data' => [
                    'verification_id' => $verification->verification_id,
                    'type' => $verification->type,
                    'status' => $verification->status,
                    'expires_at' => $verification->expires_at?->toISOString(),
                ],
                'message' => ucfirst($type) . ' verification initiated successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Initiate verification error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to initiate verification',
                'httpCode' => 500,
            ];
        }
    }

    public function verify(User $user, array $data): array
    {
        try {
            // Validate required fields
            $validator = \Illuminate\Support\Facades\Validator::make($data, [
                'verification_id' => 'required|string',
                'code' => 'required|string',
                'type' => 'required|in:email,phone',
            ]);

            if ($validator->fails()) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'data' => ['errors' => $validator->errors()],
                    'httpCode' => 422,
                ];
            }

            // Find the verification record
            $verification = Verification::where('verification_id', $data['verification_id'])
                ->where('user_id', $user->id)
                ->where('type', $data['type'])
                ->first();

            if (!$verification) {
                return [
                    'success' => false,
                    'message' => 'Verification not found',
                    'httpCode' => 404,
                ];
            }

            // Check if already completed
            if ($verification->isCompleted()) {
                return [
                    'success' => false,
                    'message' => ucfirst($data['type']) . ' is already verified',
                    'httpCode' => 400,
                ];
            }

            // Check if expired
            if ($verification->expires_at && now()->gt($verification->expires_at)) {
                $verification->update(['status' => Verification::STATUS_EXPIRED]);
                return [
                    'success' => false,
                    'message' => 'Verification code has expired. Please request a new one.',
                    'httpCode' => 400,
                ];
            }

            // Verify the code
            if ($verification->code !== $data['code']) {
                return [
                    'success' => false,
                    'message' => 'Invalid verification code',
                    'httpCode' => 400,
                ];
            }

            // Update verification as completed
            $verification->update([
                'status' => Verification::STATUS_COMPLETED,
                'verified_at' => now(),
            ]);

            return [
                'success' => true,
                'data' => [
                    'verified' => true,
                    'type' => $data['type'],
                    'verified_at' => $verification->verified_at->toISOString(),
                ],
                'message' => ucfirst($data['type']) . ' verification completed successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Verify error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Verification failed',
                'httpCode' => 500,
            ];
        }
    }

    public function getStatus(User $user): array
    {
        try {
            $verifications = Verification::where('user_id', $user->id)->get();

            $statusData = [
                'email' => ['verified' => false, 'pending' => false, 'is_paid' => false],
                'phone' => ['verified' => false, 'pending' => false, 'is_paid' => false],
                'identity' => ['verified' => false, 'pending' => false, 'is_paid' => false, 'kyc_submitted' => false],
            ];

            foreach ($verifications as $verification) {
                $type = $verification->type;

                if ($type === 'identity') {
                    $statusData[$type] = [
                        'verified' => $verification->status === 'completed',
                        'pending' => $verification->status === 'pending' || $verification->status === 'processing',
                        'is_paid' => $verification->is_paid,
                        'kyc_submitted' => !empty($verification->selfie_with_id_path) && !empty($verification->id_document_path),
                        'kyc_verified' => $verification->kyc_verified,
                        'verified_at' => $verification->verified_at?->toISOString(),
                        'kyc_verified_at' => $verification->kyc_verified_at?->toISOString(),
                    ];
                } else {
                    $statusData[$type] = [
                        'verified' => $verification->status === 'completed',
                        'pending' => $verification->status === 'pending',
                        'verified_at' => $verification->verified_at?->toISOString(),
                    ];
                }
            }

            // For identity verification, only show full details if paid
            if (!$statusData['identity']['is_paid']) {
                $statusData['identity'] = [
                    'is_paid' => false,
                    'verified' => false,
                    'pending' => false,
                    'kyc_submitted' => false,
                    'message' => 'Payment required to access identity verification status',
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'verifications' => $statusData,
                ],
                'message' => 'Verification status retrieved successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Get verification status error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve verification status',
                'httpCode' => 500,
            ];
        }
    }

    public function getDetails(): array
    {
        try {
            return [
                'success' => true,
                'data' => [
                    'id' => 'verification-basic',
                    'name' => 'Identity Verification',
                    'description' => 'Complete identity and credential verification including KYC, national identification documents, and selfie with ID',
                    'price' => 2000,
                    'currency' => 'KES',
                    'features' => [
                        'KYC verification',
                        'National ID verification',
                        'Selfie with ID verification',
                        'Address confirmation',
                        'Criminal background check',
                        'Reference checks',
                        'Digital badge',
                    ],
                    'duration' => 'One-time',
                    'isPopular' => true,
                ],
                'message' => 'Verification details retrieved successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Get verification details error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve details',
                'httpCode' => 500,
            ];
        }
    }

    /**
     * Generate a random verification code
     */
    private function generateVerificationCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create payment transaction for verification service
     * Verification payment could share a coupon with Accreditation or has its own coupon
     */
    public function createPaymentTransaction(User $user, string $couponCode = null): array
    {
        try {
            // Check if user already has a pending or completed verification payment
            $existingTransaction = Transaction::where('user_id', $user->id)
                ->where('purpose_type', Verification::class)
                ->whereIn('status', [Transaction::PENDING, Transaction::COMPLETED])
                ->first();

            if ($existingTransaction && $existingTransaction->status === Transaction::COMPLETED) {
                return [
                    'success' => false,
                    'message' => 'Payment already completed for verification service',
                    'httpCode' => 400,
                ];
            }

            if ($existingTransaction && $existingTransaction->status === Transaction::PENDING) {
                return [
                    'success' => true,
                    'data' => [
                        'transaction_id' => $existingTransaction->transaction_id,
                        'amount' => $existingTransaction->amount,
                        'status' => $existingTransaction->status,
                        'created_at' => $existingTransaction->created_at->toISOString(),
                    ],
                    'message' => 'Existing payment transaction found',
                    'httpCode' => 200,
                ];
            }

            $amount = 2000; // Base amount in KES
            $discount = 0;
            $appliedCoupon = null;

            // Handle coupon application (can share with Accreditation or have its own)
            if ($couponCode) {
                $coupon = \App\Models\Coupon::where('code', $couponCode)
                    ->where('is_active', true)
                    ->first();

                if ($coupon) {
                    // Check if coupon is valid and applicable
                    if ($coupon->isValid() && $coupon->meetsMinimumAmount($amount)) {
                        $discount = $coupon->calculateDiscount($amount);
                        $appliedCoupon = $coupon;
                    } else {
                        return [
                            'success' => false,
                            'message' => 'Invalid or expired coupon code',
                            'httpCode' => 400,
                        ];
                    }
                } else {
                    return [
                        'success' => false,
                        'message' => 'Coupon code not found',
                        'httpCode' => 404,
                    ];
                }
            }

            $finalAmount = max(0, $amount - $discount);

            // Create new transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'transaction_id' => 'ver_' . time() . '_' . $user->id,
                'amount' => $finalAmount,
                'currency' => 'KES',
                'status' => Transaction::PENDING,
                'type' => Transaction::INITIAL,
                'purpose_type' => Verification::class,
                'purpose_id' => null, // Will be set when verification is created
                'description' => 'Identity Verification Service Payment',
                'metadata' => [
                    'service_type' => 'verification',
                    'service_name' => 'Identity Verification',
                    'original_amount' => $amount,
                    'discount_applied' => $discount,
                    'coupon_code' => $couponCode,
                    'coupon_id' => $appliedCoupon ? $appliedCoupon->id : null,
                ],
            ]);

            // Increment coupon usage if applied
            if ($appliedCoupon) {
                $appliedCoupon->incrementUsage();
            }

            return [
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                    'original_amount' => $amount,
                    'discount_applied' => $discount,
                    'coupon_applied' => $couponCode ? true : false,
                    'status' => $transaction->status,
                    'created_at' => $transaction->created_at->toISOString(),
                ],
                'message' => 'Payment transaction created successfully',
                'httpCode' => 201,
            ];
        } catch (\Exception $e) {
            Log::error('Create verification payment transaction error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create payment transaction',
                'httpCode' => 500,
            ];
        }
    }

    /**
     * Complete payment transaction
     */
    public function completePaymentTransaction(User $user, string $transactionId): array
    {
        try {
            $transaction = Transaction::where('transaction_id', $transactionId)
                ->where('user_id', $user->id)
                ->where('purpose_type', Verification::class)
                ->first();

            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction not found',
                    'httpCode' => 404,
                ];
            }

            if ($transaction->status === Transaction::COMPLETED) {
                return [
                    'success' => false,
                    'message' => 'Transaction already completed',
                    'httpCode' => 400,
                ];
            }

            DB::transaction(function () use ($transaction, $user) {
                // Update transaction status
                $transaction->update([
                    'status' => Transaction::COMPLETED,
                    'processed_at' => now(),
                ]);

                // Update verification record to mark as paid
                $verification = Verification::where('user_id', $user->id)
                    ->where('type', Verification::TYPE_IDENTITY)
                    ->first();

                if ($verification) {
                    $verification->update([
                        'is_paid' => true,
                        'purpose_id' => $verification->id, // Link transaction to verification
                    ]);

                    // Update transaction with verification ID
                    $transaction->update([
                        'purpose_id' => $verification->id,
                    ]);
                }
            });

            return [
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'status' => $transaction->status,
                    'processed_at' => $transaction->processed_at->toISOString(),
                ],
                'message' => 'Payment transaction completed successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Complete verification payment transaction error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to complete payment transaction',
                'httpCode' => 500,
            ];
        }
    }

    /**
     * Submit KYC data for identity verification
     */
    public function submitKYC(User $user, array $data): array
    {
        try {
            // Find the user's identity verification
            $verification = Verification::where('user_id', $user->id)
                ->where('type', Verification::TYPE_IDENTITY)
                ->where('is_paid', true)
                ->first();

            if (!$verification) {
                return [
                    'success' => false,
                    'message' => 'Identity verification not found or payment not completed',
                    'httpCode' => 404,
                ];
            }

            if ($verification->status !== Verification::STATUS_PENDING) {
                return [
                    'success' => false,
                    'message' => 'KYC data can only be submitted for pending verifications',
                    'httpCode' => 400,
                ];
            }

            // Validate required fields
            $validator = \Illuminate\Support\Facades\Validator::make($data, [
                'id_number' => 'required|string|max:50',
                'id_type' => 'required|in:passport,national_id,drivers_license',
                'full_name' => 'required|string|max:255',
                'date_of_birth' => 'required|date|before:today',
                'nationality' => 'required|string|max:100',
                'address' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'data' => ['errors' => $validator->errors()],
                    'httpCode' => 422,
                ];
            }

            // Update verification with KYC data
            $verification->update([
                'id_number' => $data['id_number'],
                'id_type' => $data['id_type'],
                'full_name' => $data['full_name'],
                'date_of_birth' => $data['date_of_birth'],
                'nationality' => $data['nationality'],
                'address' => $data['address'],
                'status' => Verification::STATUS_PROCESSING,
            ]);

            return [
                'success' => true,
                'data' => [
                    'verification_id' => $verification->verification_id,
                    'status' => $verification->status,
                    'kyc_submitted' => true,
                ],
                'message' => 'KYC data submitted successfully. Please upload your documents.',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Submit KYC error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to submit KYC data',
                'httpCode' => 500,
            ];
        }
    }

    /**
     * Upload KYC document (selfie with ID or national identification document)
     */
    public function uploadKYCDocument(User $user, $file, string $type): array
    {
        try {
            // Validate file type
            if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'application/pdf'])) {
                return [
                    'success' => false,
                    'message' => 'Only JPEG, PNG images and PDF documents are allowed',
                    'httpCode' => 400,
                ];
            }

            // Validate file size (max 5MB)
            if ($file->getSize() > 5 * 1024 * 1024) {
                return [
                    'success' => false,
                    'message' => 'File size must not exceed 5MB',
                    'httpCode' => 400,
                ];
            }

            // Validate document type
            if (!in_array($type, ['selfie_with_id', 'id_document'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid document type. Only selfie_with_id and id_document are allowed',
                    'httpCode' => 400,
                ];
            }

            // Find the user's identity verification
            $verification = Verification::where('user_id', $user->id)
                ->where('type', Verification::TYPE_IDENTITY)
                ->where('is_paid', true)
                ->first();

            if (!$verification) {
                return [
                    'success' => false,
                    'message' => 'Identity verification not found or payment not completed',
                    'httpCode' => 404,
                ];
            }

            if ($verification->status !== Verification::STATUS_PROCESSING) {
                return [
                    'success' => false,
                    'message' => 'Document upload is only allowed during processing stage',
                    'httpCode' => 400,
                ];
            }

            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = 'kyc_' . $user->id . '_' . $type . '_' . time() . '.' . $extension;
            $path = 'kyc_documents/' . $filename;

            // Store file
            $file->storeAs('kyc_documents', $filename, 'public');

            // Update verification record
            if ($type === 'selfie_with_id') {
                $verification->update(['selfie_with_id_path' => $path]);
            } elseif ($type === 'id_document') {
                $verification->update(['id_document_path' => $path]);
            }

            return [
                'success' => true,
                'data' => [
                    'document_type' => $type,
                    'file_path' => $path,
                    'uploaded_at' => now()->toISOString(),
                ],
                'message' => ucfirst(str_replace('_', ' ', $type)) . ' uploaded successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Upload KYC document error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to upload document',
                'httpCode' => 500,
            ];
        }
    }

    /**
     * Verify KYC (admin function) - Admin can only mark someone verified if the fee is paid in full
     */
    public function verifyKYC(User $user, string $verificationId, bool $approved, string $notes = null): array
    {
        try {
            $verification = Verification::where('verification_id', $verificationId)
                ->where('type', Verification::TYPE_IDENTITY)
                ->first();

            if (!$verification) {
                return [
                    'success' => false,
                    'message' => 'Verification not found',
                    'httpCode' => 404,
                ];
            }

            // Check if payment is completed before allowing approval
            if ($approved && !$verification->is_paid) {
                return [
                    'success' => false,
                    'message' => 'Cannot approve verification: Payment has not been completed in full',
                    'httpCode' => 402, // Payment Required
                ];
            }

            // Ensure KYC documents are uploaded before approval
            if ($approved && (empty($verification->selfie_with_id_path) || empty($verification->id_document_path))) {
                return [
                    'success' => false,
                    'message' => 'Cannot approve verification: Required KYC documents (selfie with ID and national identification document) must be uploaded',
                    'httpCode' => 400,
                ];
            }

            if ($approved) {
                $verification->update([
                    'kyc_verified' => true,
                    'kyc_verified_at' => now(),
                    'kyc_verification_notes' => $notes,
                    'status' => Verification::STATUS_COMPLETED,
                    'verified_at' => now(),
                ]);
            } else {
                $verification->update([
                    'kyc_verified' => false,
                    'kyc_verification_notes' => $notes,
                    'status' => Verification::STATUS_FAILED,
                ]);
            }

            return [
                'success' => true,
                'data' => [
                    'verification_id' => $verification->verification_id,
                    'kyc_verified' => $verification->kyc_verified,
                    'status' => $verification->status,
                    'verified_at' => $verification->verified_at?->toISOString(),
                    'kyc_verified_at' => $verification->kyc_verified_at?->toISOString(),
                ],
                'message' => 'KYC verification ' . ($approved ? 'approved' : 'rejected') . ' successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Verify KYC error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to verify KYC',
                'httpCode' => 500,
            ];
        }
    }
}

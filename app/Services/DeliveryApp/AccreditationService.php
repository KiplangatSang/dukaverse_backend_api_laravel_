<?php

namespace App\Services\DeliveryApp;

use App\Models\User;
use App\Models\Accreditation;
use App\Models\Tier;
use App\Models\Transaction;
use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Services\BaseService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AccreditationService extends BaseService
{
    public function __construct(
        StoreFileResource $storeFileResource,
       private ResponseHelper $responseHelper,
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }
    public function apply(User $user, array $data)
    {
        try {
            // Check if user has paid for accreditation service
            $existingAccreditation = Accreditation::where('user_id', $user->id)->first();

            if (!$existingAccreditation || !$existingAccreditation->is_paid) {
                return ResponseHelper::error('Payment required for accreditation service. Please complete payment first.', [], 402);
            }

            // Check if already accredited
            if ($existingAccreditation->status === 'accredited') {
                return ResponseHelper::error('User is already accredited', [], 400);
            }

            // Update accreditation application
            $existingAccreditation->update([
                'business_type' => $data['business_type'],
                'years_experience' => $data['years_experience'],
                'specializations' => json_encode($data['specializations']),
                'references' => json_encode($data['references']),
                'status' => 'pending_review',
                'applied_at' => now(),
            ]);

            return ResponseHelper::respond([
                'application_id' => $existingAccreditation->id,
                'status' => 'pending_review',
                'submitted_at' => $existingAccreditation->applied_at->toISOString(),
                'estimated_completion' => now()->addDays(7)->toISOString(),
            ], 'Accreditation application submitted successfully', 201);
        } catch (\Exception $e) {
            Log::error('Apply for accreditation error: ' . $e->getMessage());
            return ResponseHelper::error('Failed to submit application', [], 500);
        }
    }

    public function getStatus(User $user)
    {
        try {
            $accreditation = Accreditation::where('user_id', $user->id)->first();

            if (!$accreditation) {
                return $this->responseHelper->error('No accreditation application found', [], 404);
            }

            if (!$accreditation->is_paid) {
                return $this->responseHelper->error('Payment required to access accreditation status', [], 402);
            }

            return $this->responseHelper->respond([
                'application_id' => $accreditation->accreditation_id,
                'status' => $accreditation->status,
                'submitted_at' => $accreditation->applied_at?->toISOString(),
                'estimated_completion' => $accreditation->applied_at?->addDays(7)->toISOString(),
                'documents_required' => Accreditation::REQUIRED_DOCUMENTS,
                'documents_submitted' => $accreditation->getDocumentStatus(),
                'expires_at' => $accreditation->expires_at?->toISOString(),
                'renewal_pending' => $accreditation->status === Accreditation::STATUS_RENEWAL_PENDING,
            ], 'Accreditation status retrieved successfully', 200);
        } catch (\Exception $e) {
            Log::error('Get accreditation status error: ' . $e->getMessage());
            return $this->responseHelper->error('Failed to retrieve status', [], 500);
        }
    }

    public function uploadDocument(User $user, $file, string $type)
    {
        try {
            $accreditation = Accreditation::where('user_id', $user->id)->first();

            if (!$accreditation || !$accreditation->is_paid) {
                return ResponseHelper::error('Payment required to upload documents', [], 402);
            }

            // Validate file type
            $allowedTypes = ['business_license', 'insurance', 'background_check', 'training_certificate'];
            if (!in_array($type, $allowedTypes)) {
                return ResponseHelper::error('Invalid document type', [], 400);
            }

            // Store file
            $filename = $type . '_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('accreditation_docs', $filename, 'public');

            // Update accreditation record
            $accreditation->update([
                $type . '_path' => $path,
                $type . '_uploaded_at' => now(),
            ]);

            return ResponseHelper::respond([
                'document_id' => 'doc_' . time(),
                'filename' => $filename,
                'path' => $path,
                'type' => $type,
                'uploaded_at' => now()->toISOString(),
            ], 'Document uploaded successfully', 200);
        } catch (\Exception $e) {
            Log::error('Upload document error: ' . $e->getMessage());
            return ResponseHelper::error('Failed to upload document', [], 500);
        }
    }

    public function getRequirements()
    {
        try {
            return $this->responseHelper->respond([
                'requirements' => [
                    [
                        'type' => 'business_license',
                        'name' => 'Business License',
                        'description' => 'Valid business license or registration certificate',
                        'required' => true,
                        'max_size' => '5MB',
                        'allowed_formats' => ['pdf', 'jpg', 'jpeg', 'png'],
                    ],
                    [
                        'type' => 'insurance',
                        'name' => 'Insurance Certificate',
                        'description' => 'Valid insurance certificate covering delivery operations',
                        'required' => true,
                        'max_size' => '5MB',
                        'allowed_formats' => ['pdf', 'jpg', 'jpeg', 'png'],
                    ],
                    [
                        'type' => 'background_check',
                        'name' => 'Background Check',
                        'description' => 'Criminal background check certificate',
                        'required' => true,
                        'max_size' => '5MB',
                        'allowed_formats' => ['pdf', 'jpg', 'jpeg', 'png'],
                    ],
                    [
                        'type' => 'training_certificate',
                        'name' => 'Training Certificate',
                        'description' => 'Delivery training or certification',
                        'required' => false,
                        'max_size' => '5MB',
                        'allowed_formats' => ['pdf', 'jpg', 'jpeg', 'png'],
                    ],
                ],
            ], 'Accreditation requirements retrieved successfully', 200);
        } catch (\Exception $e) {
            Log::error('Get requirements error: ' . $e->getMessage());
            return $this->responseHelper->error('Failed to retrieve requirements', [], 500);
        }
    }

    public function renew(User $user)
    {
        try {
            $accreditation = Accreditation::where('user_id', $user->id)->first();

            if (!$accreditation) {
                return ResponseHelper::error('No accreditation found to renew', [], 404);
            }

            if (!$accreditation->is_paid) {
                return ResponseHelper::error('Payment required to renew accreditation', [], 402);
            }

            if ($accreditation->status !== Accreditation::STATUS_COMPLETED) {
                return ResponseHelper::error('Only completed accreditations can be renewed', [], 400);
            }

            if ($accreditation->isExpiringSoon(30)) {
                $accreditation->initiateRenewal();

                return ResponseHelper::respond([
                    'renewal_id' => $accreditation->renewal_id,
                    'status' => $accreditation->status,
                    'expires_at' => $accreditation->expires_at?->toISOString(),
                    'renewal_date' => $accreditation->renewal_date?->toISOString(),
                ], 'Accreditation renewal initiated successfully', 200);
            } else {
                return ResponseHelper::error('Accreditation is not eligible for renewal yet. Renewal is only allowed within 30 days of expiration.', [], 400);
            }
        } catch (\Exception $e) {
            Log::error('Renew accreditation error: ' . $e->getMessage());
            return ResponseHelper::error('Failed to initiate renewal', [], 500);
        }
    }

    public function getDetails()
    {
        try {
            $accreditationTiers = Tier::where('tier', "accreditation")->get();
            return ResponseHelper::respond($accreditationTiers, 'Accreditation details retrieved successfully', 200);
        } catch (\Exception $e) {
            Log::error('Get accreditation details error: ' . $e->getMessage());
            return ResponseHelper::error('Failed to retrieve details', [], 500);
        }
    }
}

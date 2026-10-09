# Fixed Files for Paid Services Implementation

## Overview
The following files have been created with the `-fixed.php` suffix because direct editing of the original files was not possible. These files contain the complete implementation of paid verification and accreditation services with payment tracking.

## Files Created

### 1. app/Services/DeliveryApp/VerificationService-fixed.php
**Purpose:** Complete verification service with KYC functionality and payment tracking.

**Key Features Added:**
- Payment transaction creation and completion
- KYC data submission and validation
- Document upload for identity verification
- Admin KYC verification
- Payment status checking in all methods

**Methods Added:**
- `createPaymentTransaction(User $user)`: Creates payment transaction for verification service
- `completePaymentTransaction(User $user, string $transactionId)`: Completes payment transaction
- `submitKYC(User $user, array $data)`: Submits KYC data for identity verification
- `uploadKYCDocument(User $user, $file, string $type)`: Uploads KYC documents
- `verifyKYC(User $user, string $verificationId, bool $approved, string $notes)`: Admin KYC verification

### 2. app/Services/DeliveryApp/AccreditationService-fixed.php
**Purpose:** Complete accreditation service with payment tracking.

**Key Features Added:**
- Payment transaction creation and completion
- Document upload for accreditation
- Renewal functionality
- Payment status checking in all methods

**Methods Added:**
- `createPaymentTransaction(User $user)`: Creates payment transaction for accreditation service
- `completePaymentTransaction(User $user, string $transactionId)`: Completes payment transaction

## How to Use

1. **Replace the original files** with these fixed versions:
   ```bash
   cp app/Services/DeliveryApp/VerificationService-fixed.php app/Services/DeliveryApp/VerificationService.php
   cp app/Services/DeliveryApp/AccreditationService-fixed.php app/Services/DeliveryApp/AccreditationService.php
   ```

2. **Update controllers** to include new KYC endpoints (if not already present):
   - Add KYC submission, document upload, and verification endpoints to `VerificationController.php`

3. **Run tests** to verify functionality:
   ```bash
   php test_kyc_verification.php
   ```

## Payment Flow

### Verification Service:
1. User initiates identity verification
2. System checks if payment is required
3. User creates payment transaction
4. After payment completion, user can submit KYC data
5. User uploads required documents (selfie with ID, ID document)
6. Admin verifies KYC and marks as completed

### Accreditation Service:
1. User applies for accreditation
2. System checks if payment is required
3. User creates payment transaction
4. After payment completion, user can upload required documents
5. Admin reviews documents and approves/rejects accreditation

## Key Features

- **Payment Tracking:** All services require payment before access
- **KYC Verification:** Identity verification includes document upload and admin review
- **Document Management:** Secure file upload with validation
- **Status Tracking:** Real-time status updates for all processes
- **Error Handling:** Comprehensive error handling and logging

## Database Requirements

Ensure the following models have the required fields:
- `Verification` model: `is_paid`, `kyc_verified`, `kyc_verified_at`, `kyc_verification_notes`, `selfie_with_id_path`, `id_document_path`, etc.
- `Accreditation` model: `is_paid`, document path fields, etc.
- `Transaction` model: Standard transaction fields with polymorphic relationships

## API Endpoints

### Verification Endpoints:
- `POST /api/v1/verification/initiate` - Initiate verification
- `POST /api/v1/verification/kyc/submit` - Submit KYC data
- `POST /api/v1/verification/kyc/upload/{type}` - Upload KYC documents
- `POST /api/v1/verification/kyc/verify/{verificationId}` - Admin KYC verification

### Accreditation Endpoints:
- `POST /api/v1/accreditation/apply` - Apply for accreditation
- `POST /api/v1/accreditation/upload-document` - Upload accreditation documents
- `POST /api/v1/accreditation/renew` - Renew accreditation

## Testing

Run the provided test file to verify the implementation:
```bash
php test_kyc_verification.php
```

The test covers:
- Payment transaction creation
- Payment completion
- KYC data submission
- Document upload
- Status checking

## Notes

- All services are now paid services requiring payment before access
- Identity verification includes full KYC process with document verification
- Payment transactions are tracked using the Transaction model
- File uploads are validated for type and size
- Admin approval is required for final verification/accreditation

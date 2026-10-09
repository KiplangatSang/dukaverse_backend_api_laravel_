<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    protected $fillable = [
        'user_id',
        'verification_id',
        'type',
        'status',
        'code',
        'expires_at',
        'is_paid',
        'id_number',
        'id_type',
        'full_name',
        'date_of_birth',
        'nationality',
        'address',
        'selfie_with_id_path',
        'id_document_path',
        'kyc_verified',
        'kyc_verified_at',
        'kyc_verification_notes',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'kyc_verified_at' => 'datetime',
        'is_paid' => 'boolean',
        'kyc_verified' => 'boolean',
    ];

    // Constants
    const TYPE_EMAIL = 'email';
    const TYPE_PHONE = 'phone';
    const TYPE_IDENTITY = 'identity';

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';

    /**
     * Check if verification is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if verification is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if verification is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if verification has failed
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if verification has expired
     */
    public function hasExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }
}

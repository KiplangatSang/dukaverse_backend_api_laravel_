<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accreditation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'accreditation_id',
        'business_type',
        'years_experience',
        'specializations',
        'references',
        'status',
        'is_paid',
        'business_license_path',
        'insurance_path',
        'background_check_path',
        'training_certificate_path',
        'expires_at',
        'renewal_id',
        'renewal_date',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'specializations' => 'array',
        'references' => 'array',
        'expires_at' => 'datetime',
        'renewal_date' => 'datetime',
        'approved_at' => 'datetime',
        'is_paid' => 'boolean',
    ];

    // Business Types
    const BUSINESS_TYPE_INDIVIDUAL = 'individual';
    const BUSINESS_TYPE_BUSINESS = 'business';

    const BUSINESS_TYPES = [
        self::BUSINESS_TYPE_INDIVIDUAL,
        self::BUSINESS_TYPE_BUSINESS,
    ];

    // Accreditation Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_RENEWAL_PENDING = 'renewal_pending';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_EXPIRED,
        self::STATUS_RENEWAL_PENDING,
    ];

    // Required Documents
    const REQUIRED_DOCUMENTS = [
        'business_license',
        'insurance',
        'background_check',
        'training_certificate',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function isRenewalPending(): bool
    {
        return $this->status === self::STATUS_RENEWAL_PENDING;
    }

    public function markAsCompleted($approvedBy = null)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'approved_at' => now(),
            'approved_by' => $approvedBy,
            'expires_at' => now()->addYear(),
        ]);
    }

    public function markAsFailed($reason = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'rejection_reason' => $reason,
        ]);
    }

    public function markAsProcessing()
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
        ]);
    }

    public function markAsExpired()
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);
    }

    public function initiateRenewal()
    {
        $this->update([
            'status' => self::STATUS_RENEWAL_PENDING,
            'renewal_date' => now(),
            'renewal_id' => 'renew_' . time() . '_' . $this->id,
        ]);
    }

    public function hasAllRequiredDocuments(): bool
    {
        return !empty($this->business_license_path) &&
               !empty($this->insurance_path) &&
               !empty($this->background_check_path) &&
               !empty($this->training_certificate_path);
    }

    public function getDocumentStatus(): array
    {
        return [
            'business_license' => !empty($this->business_license_path),
            'insurance' => !empty($this->insurance_path),
            'background_check' => !empty($this->background_check_path),
            'training_certificate' => !empty($this->training_certificate_path),
        ];
    }

    public function isExpiringSoon($days = 30): bool
    {
        return $this->expires_at && $this->expires_at->diffInDays(now()) <= $days;
    }


}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    // Subscription-specific transaction statuses
    const PENDING = 'pending';
    const COMPLETED = 'completed';
    const FAILED = 'failed';
    const REFUNDED = 'refunded';

    const STATUSES = [
        self::PENDING,
        self::COMPLETED,
        self::FAILED,
        self::REFUNDED,
    ];

    // Transaction types
    const INITIAL = 'initial';
    const RENEWAL = 'renewal';
    const UPGRADE = 'upgrade';
    const DOWNGRADE = 'downgrade';
    const REFUND = 'refund';

    const TYPES = [
        self::INITIAL,
        self::RENEWAL,
        self::UPGRADE,
        self::DOWNGRADE,
        self::REFUND,
    ];

    // Legacy constants for backward compatibility
    const INTERNAL             = "internal";
    const EXTERNAL_TO_INTERNAL = "external_to_internal";
    const INTERNAL_TO_EXTERNAL = "internal_to_external";
    const EXTERNAL             = "external";

    const TRASACTION_TYPES = [
        self::INTERNAL,
        self::EXTERNAL_TO_INTERNAL,
        self::INTERNAL_TO_EXTERNAL,
        self::EXTERNAL,
    ];

    const AWAITING_AUTHORIZATION = 'awaiting authorization';
    const UNPAID                 = 'not paid';
    const PAID                   = 'paid';
    const AWAITING_REFUND        = 'awaiting refund';

    const TRANSACTION_STATUS = [
        self::AWAITING_AUTHORIZATION,
        self::FAILED,
        self::UNPAID,
        self::PAID,
        self::AWAITING_REFUND,
        self::REFUNDED,
    ];

    public function getTransactionType($payment_method): int
    {
        switch ($payment_method) {
            case $payment_method === "MPESA" || $payment_method === "Mpesa" || $payment_method === "mpesa":
                return self::EXTERNAL_TO_INTERNAL;
                break;
            case $payment_method === "CASH" || $payment_method === "Cash" || $payment_method === "cash":
                return self::EXTERNAL;
                break;
            default:
                return self::EXTERNAL;
                break;
        }
    }

    // Relationships
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Legacy relationships for backward compatibility
    public function ownerable()
    {
        return $this->morphTo();
    }

    public function transactionable()
    {
        return $this->morphTo();
    }

    public function purposeable()
    {
        return $this->morphTo();
    }

    public function sendable()
    {
        return $this->morphTo();
    }

    public function receivable()
    {
        return $this->morphTo();
    }

    public function senderAccount()
    {
        return $this->belongsTo(Account::class, "sender_accounts_id");
    }

    public function receiverAccount()
    {
        return $this->belongsTo(Account::class, "receiver_accounts_id");
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class, 'transaction_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::PENDING);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::FAILED);
    }

    public function scopeForSubscription($query, $subscriptionId)
    {
        return $query->where('subscription_id', $subscriptionId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function isCompleted(): bool
    {
        return $this->status === self::COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::FAILED;
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => self::COMPLETED,
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed()
    {
        $this->update([
            'status' => self::FAILED,
            'processed_at' => now(),
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryVehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'delivery_person_id',
        'vehicle_type',
        'make',
        'model',
        'year',
        'color',
        'license_plate',
        'registration_number',
        'insurance_policy_number',
        'insurance_expiry_date',
        'capacity_weight',
        'capacity_volume',
        'is_primary',
        'status',
        'documents_verified',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'insurance_expiry_date' => 'date',
        'capacity_weight' => 'decimal:2',
        'capacity_volume' => 'decimal:2',
        'is_primary' => 'boolean',
        'documents_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function deliveryPerson(): BelongsTo
    {
        return $this->belongsTo(DeliveryPerson::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('documents_verified', true);
    }

    // Methods
    public function markAsPrimary()
    {
        // Remove primary status from other vehicles
        $this->deliveryPerson->vehicles()
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        $this->update(['is_primary' => true]);
    }

    public function verifyDocuments($verifiedBy)
    {
        $this->update([
            'documents_verified' => true,
            'verified_at' => now(),
            'verified_by' => $verifiedBy,
        ]);
    }

    public function isVerified()
    {
        return $this->documents_verified;
    }

    public function isPrimary()
    {
        return $this->is_primary;
    }

    public function isInsuranceExpired()
    {
        return $this->insurance_expiry_date && $this->insurance_expiry_date->isPast();
    }
}

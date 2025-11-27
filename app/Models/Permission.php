<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Permission extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'ownerable_id',
        'ownerable_type',
    ];

    /**
     * Get the owning ownerable model.
     */

    public function tiers()
    {
        return $this->belongsToMany(\App\Models\Tier::class, 'permission_tier');
    }

    public function ownerable(): MorphTo
    {
        return $this->morphTo();
    }
}

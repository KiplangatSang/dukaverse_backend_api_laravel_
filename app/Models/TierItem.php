<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TierItem extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function tier()
    {
        return $this->belongsTo(Tier::class, 'tier_id');
    }

}

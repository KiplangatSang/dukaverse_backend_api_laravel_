<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    const COLD_LEAD    = "cold";
    const LOST_LEAD    = "lost";
    const WON_LEAD     = "won";
    const PENDING_LEAD = "pending";

    const LEAD_STATUS_COLLECTION = [
        self::PENDING_LEAD => self::PENDING_LEAD,
        self::COLD_LEAD    => self::COLD_LEAD,
        self::LOST_LEAD    => self::LOST_LEAD,
        self::WON_LEAD     => self::WON_LEAD,
    ];

    const LEAD_STATUS = [
        self::PENDING_LEAD,
        self::COLD_LEAD,
        self::LOST_LEAD,
        self::WON_LEAD,
    ];

    const NO_PROFILE = "https://storage.googleapis.com/dukaverse-e4f47.appspot.com/app/noprofile.png";
    public function ownerable()
    {
        return $this->morphTo();
    }

    public function leadable()
    {
        return $this->morphTo();
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');

    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'sendable');
    }

}

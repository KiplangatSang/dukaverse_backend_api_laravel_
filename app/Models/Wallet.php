<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model {
    use HasFactory, SoftDeletes;
    const WALLET_STATUS_FROZEN   = "frozen";
    const WALLET_STATUS_INACTIVE = "inactive";
    const WALLET_STATUS_ACTIVE   = "active";

    const WALLET_STATUS = [
        self::WALLET_STATUS_ACTIVE, //0 IS THE DEAULT WALLET STATUS
        self::WALLET_STATUS_FROZEN,
        self::WALLET_STATUS_INACTIVE,
    ];

    protected $guarded = [];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function walletable()
    {
        return $this->morphTo();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, "wallet_id");
    }

}

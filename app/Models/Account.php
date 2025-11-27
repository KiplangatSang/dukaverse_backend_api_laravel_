<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model {
    use HasFactory, SoftDeletes;
    //
    protected $guarded = [];

    public function accountable()
    {
        # code...
        return $this->morphTo();
    }

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function senderTransactions()
    {
        return $this->hasMany(Transaction::class, 'sender_accounts_id');
    }
    public function receiverTransactions()
    {
        return $this->hasMany(Transaction::class, 'receiver_accounts_id');
    }
}

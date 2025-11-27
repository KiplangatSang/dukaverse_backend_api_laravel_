<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Todo extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    const USER_TODO_TYPES    = 'user';
    const ACCOUNT_TODO_TYPES = 'account';

    const TODO_TYPES = [
        self::USER_TODO_TYPES,
        self::ACCOUNT_TODO_TYPES,
    ];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function todoable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function creator()
    {
        return $this->user();
    }

    public function assigned()
    {
        return $this->belongsTo(User::class, "assigned_to");
    }

}

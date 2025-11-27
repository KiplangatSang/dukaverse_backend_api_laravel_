<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SessionAccount extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function sessionable()
    {
        # code...
        return $this->morphTo();
    }

    public function user()
    {
        # code...
        return $this->belongsTo(User::class, "user_id");
    }

    public function ownerable()
    {
        return $this->morphTo();
    }

}

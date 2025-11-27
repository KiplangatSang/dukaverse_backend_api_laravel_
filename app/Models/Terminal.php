<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Terminal extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    const SALES_TERMINAL_TYPE = 'sales';

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function terminalable()
    {
        return $this->morphTo();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'terminal_user', 'terminal_id', 'user_id');
    }

}

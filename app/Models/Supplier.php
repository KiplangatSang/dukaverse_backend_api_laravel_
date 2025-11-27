<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model {
    use HasFactory, SoftDeletes;

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function supplierable()
    {
        return $this->morphTo();
    }
    public function supplies()
    {
        return $this->hasMany(Supplier::class, 'supplier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Platform extends Model
{
    /** @use HasFactory<\Database\Factories\PlatformFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany(User::class, 'platforms_users', 'platform_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function ecommerces()
    {
        return $this->hasMany(Ecommerce::class);
    }

    public function getEcommerceCountAttribute()
    {
        return $this->ecommerces()->count();
    }

    public function getUserCountAttribute()
    {
        return $this->users()->count();
    }

    public function locations()
    {
        return $this->morphMany(Location::class, 'locationable');
    }
}

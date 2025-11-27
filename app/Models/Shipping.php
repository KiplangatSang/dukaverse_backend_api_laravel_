<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipping extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [];

    public function owner()
    {
        return $this->morphTo();
    }

    public function ownerable()
    {
        return $this->morphTo();
    }

}

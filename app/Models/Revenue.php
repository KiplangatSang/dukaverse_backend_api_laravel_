<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Revenue extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function revenueable()
    {
        # code...
        return $this->morphTo();
    }

    public function sourceable()
    {
        # code...
        return $this->morphTo();
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RetailSetting extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function retail()
    {

        return $this->belongsTo(Retail::class, 'retail_id');

    }

}

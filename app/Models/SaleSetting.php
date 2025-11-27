<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleSetting extends Model {
    use HasFactory, SoftDeletes;

    const VAT_PERCENTAGE = 16;
    const CURRENCY       = [
        "country"  => "Kenya",
        "currency" => "KSH",
    ];
    const REQUIRED_WHEN_BELOW = 10;

    public function salesettingable()
    {

        return $this->morphTo();

    }

    protected $casts = [
        'vat_percentage' => 'float',
        'currency'       => 'array',
    ];

    public function ownerable()
    {
        return $this->morphTo();
    }

}

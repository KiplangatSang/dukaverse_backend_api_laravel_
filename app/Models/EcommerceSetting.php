<?php
namespace App\Models;

use App\Models\Ecommerce;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcommerceSetting extends Model {
    use HasFactory, SoftDeletes;

    const NOTIFICATION_CHANNELS = [
        'sms'                => 'sms',
        'email'              => 'email',
        'push notifications' => "push notifications",
    ];

    protected $guarded = [];

    public function ecommerce()
    {
        return $this->belongsTo(Ecommerce::class, 'ecommerce_id');
    }

}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model {
    use HasFactory, SoftDeletes;

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
    ];

    protected $guarded = [];

    public function messageable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function retail()
    {
        return $this->belongsTo(Retail::class, 'retail_id');
    }

    public function supplier()
    {
        # code...
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function sender()
    {
        # code...
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replier()
    {
        # code...
        return $this->belongsTo(User::class, 'replier_id');
    }
}

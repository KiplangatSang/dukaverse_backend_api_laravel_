<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content',
        'email_config_id',
        'is_default',
        'active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'active' => 'boolean',
    ];

    public function emailConfig()
    {
        return $this->belongsTo(EmailConfig::class);
    }
}

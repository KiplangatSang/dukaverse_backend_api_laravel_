<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'trigger_event',
        'subject',
        'body',
        'email_config_id',
        'conditions',
        'delay_minutes',
        'active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'active' => 'boolean',
        'delay_minutes' => 'integer',
    ];

    public function emailConfig()
    {
        return $this->belongsTo(EmailConfig::class);
    }
}

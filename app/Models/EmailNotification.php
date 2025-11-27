<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_config_id',
        'message_id',
        'subject',
        'body',
        'from_email',
        'from_name',
        'to',
        'attachments',
        'received_at',
        'processed',
    ];

    protected $casts = [
        'to' => 'array',
        'attachments' => 'array',
        'received_at' => 'datetime',
        'processed' => 'boolean',
    ];

    public function emailConfig()
    {
        return $this->belongsTo(EmailConfig::class);
    }
}

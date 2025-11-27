<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'imap_username',
        'imap_password',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'from_email',
        'from_name',
        'active',
        'no_reply_email',
        'no_reply_name',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function emailNotifications()
    {
        return $this->hasMany(EmailNotification::class);
    }

    public function emailSignatures()
    {
        return $this->hasMany(EmailSignature::class);
    }

    public function autoEmails()
    {
        return $this->hasMany(AutoEmail::class);
    }
}

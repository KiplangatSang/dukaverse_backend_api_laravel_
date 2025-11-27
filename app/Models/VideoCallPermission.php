<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoCallPermission extends Model
{
    protected $fillable = [
        'user_id',
        'role_id',
        'can_initiate',
        'can_moderate',
        'can_record',
        'can_share_screen',
        'can_mute_others',
        'can_kick_participants',
        'can_send_messages',
        'can_upload_files'
    ];

    protected $casts = [
        'can_initiate' => 'boolean',
        'can_moderate' => 'boolean',
        'can_record' => 'boolean',
        'can_share_screen' => 'boolean',
        'can_mute_others' => 'boolean',
        'can_kick_participants' => 'boolean',
        'can_send_messages' => 'boolean',
        'can_upload_files' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function canInitiateCall(): bool
    {
        return $this->can_initiate;
    }

    public function canModerate(): bool
    {
        return $this->can_moderate;
    }

    public function canRecord(): bool
    {
        return $this->can_record;
    }
}

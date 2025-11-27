<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VideoCall extends Model
{
    protected $fillable = [
        'room_id',
        'initiator_id',
        'participants',
        'status',
        'started_at',
        'ended_at',
        'settings'
    ];

    protected $casts = [
        'participants' => 'array',
        'settings' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime'
    ];

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(VideoCallParticipant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(VideoCallMessage::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }

    public function getActiveParticipants()
    {
        return $this->participants()->whereNull('left_at')->get();
    }
}

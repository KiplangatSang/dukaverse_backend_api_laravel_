<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarNotification extends Model
{
    protected $guarded = [];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'metadata' => 'array',
    ];

    const TYPES = [
        'reminder',
        'update',
        'cancellation',
        'invitation',
        'response',
        'overdue',
        'conflict'
    ];

    const CHANNELS = ['email', 'sms', 'push', 'in_app'];
    const PRIORITIES = ['low', 'medium', 'high', 'urgent'];
    const STATUSES = ['pending', 'sent', 'failed', 'cancelled'];

    // Relationships
    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeScheduledBefore($query, $datetime)
    {
        return $query->where('scheduled_at', '<=', $datetime);
    }

    // Helper methods
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed($error = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    public function canRetry()
    {
        return $this->retry_count < 3 && $this->status === 'failed';
    }

    public function getContent()
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'priority' => $this->priority,
            'calendar_id' => $this->calendar_id,
            'metadata' => $this->metadata,
        ];
    }
}

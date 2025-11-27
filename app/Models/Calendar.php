<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Calendar extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'recurrence_end_date' => 'datetime',
        'reminder_settings' => 'array',
        'recurrence_rule' => 'array',
        'custom_fields' => 'array',
        'is_all_day' => 'boolean',
        'is_recurring' => 'boolean',
        'is_exception' => 'boolean',
        'duration_hours' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    const PRIORITIES = ['low', 'medium', 'high', 'urgent'];
    const STATUSES = ['scheduled', 'cancelled', 'completed'];
    const RECURRENCE_TYPES = ['none', 'daily', 'weekly', 'monthly', 'yearly'];
    const CATEGORIES = ['meeting', 'task', 'reminder', 'personal', 'work', 'other'];

    // Relationships
    public function ownerable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class, 'calendar_attendees', 'calendar_id', 'user_id')
            ->withPivot(['status', 'role', 'responded_at', 'response_message', 'notify_reminders', 'notify_updates'])
            ->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(CalendarNotification::class);
    }

    // Scopes
    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeUpcoming(Builder $query, $days = 7)
    {
        return $query->where('start_time', '>=', now())
            ->where('start_time', '<=', now()->addDays($days));
    }

    public function scopeOverdue(Builder $query)
    {
        return $query->where('start_time', '<', now())
            ->where('status', 'scheduled');
    }

    public function scopeByPriority(Builder $query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCategory(Builder $query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeRecurring(Builder $query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeForUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper Methods
    public function getDurationAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->diffInMinutes($this->end_time);
        }
        return 0;
    }

    public function getDurationHoursAttribute()
    {
        return $this->duration_hours ?? $this->getDurationAttribute() / 60;
    }

    public function isOverdue()
    {
        return $this->start_time < now() && $this->status === 'scheduled';
    }

    public function isUpcoming($minutes = 60)
    {
        return $this->start_time > now() && $this->start_time <= now()->addMinutes($minutes);
    }

    public function getPriorityColor()
    {
        return match($this->priority) {
            'low' => '#28a745',
            'medium' => '#ffc107',
            'high' => '#fd7e14',
            'urgent' => '#dc3545',
            default => '#6c757d'
        };
    }

    public function getStatusBadge()
    {
        return match($this->status) {
            'scheduled' => ['text' => 'Scheduled', 'color' => 'primary'],
            'completed' => ['text' => 'Completed', 'color' => 'success'],
            'cancelled' => ['text' => 'Cancelled', 'color' => 'danger'],
            default => ['text' => 'Unknown', 'color' => 'secondary']
        };
    }

    public function generateRecurringInstances(Carbon $start, Carbon $end)
    {
        if (!$this->is_recurring || !$this->recurrence_rule) {
            return collect([$this]);
        }

        $instances = collect([$this]);
        $current = $this->start_time->copy();

        while ($current <= $end && (!$this->recurrence_end_date || $current <= $this->recurrence_end_date)) {
            if ($current >= $start && $current != $this->start_time) {
                $instance = $this->replicate();
                $instance->start_time = $current;
                $instance->end_time = $current->copy()->addMinutes($this->getDurationAttribute());
                $instance->is_exception = false;
                $instances->push($instance);
            }

            // Calculate next occurrence based on recurrence rule
            switch ($this->recurrence_rule['type'] ?? 'weekly') {
                case 'daily':
                    $current->addDays($this->recurrence_rule['interval'] ?? 1);
                    break;
                case 'weekly':
                    $current->addWeeks($this->recurrence_rule['interval'] ?? 1);
                    break;
                case 'monthly':
                    $current->addMonths($this->recurrence_rule['interval'] ?? 1);
                    break;
                case 'yearly':
                    $current->addYears($this->recurrence_rule['interval'] ?? 1);
                    break;
            }
        }

        return $instances;
    }

    public function checkConflicts()
    {
        return static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->where(function ($query) {
                $query->whereBetween('start_time', [$this->start_time, $this->end_time])
                    ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                    ->orWhere(function ($q) {
                        $q->where('start_time', '<=', $this->start_time)
                            ->where('end_time', '>=', $this->end_time);
                    });
            })
            ->get();
    }

    // Boot method for automatic calculations
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($calendar) {
            // Auto-calculate duration
            if ($calendar->start_time && $calendar->end_time) {
                $calendar->duration_hours = $calendar->start_time->diffInMinutes($calendar->end_time) / 60;
            }

            // Set recurring flag
            $calendar->is_recurring = !empty($calendar->recurrence_rule) && ($calendar->recurrence_rule['type'] ?? 'none') !== 'none';
        });
    }
}

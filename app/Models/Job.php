<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    protected $table = 'job_postings';

    protected $fillable = [
        'title',
        'description',
        'requirements',
        'location',
        'job_type',
        'experience_level',
        'salary_min',
        'salary_max',
        'currency',
        'department',
        'is_active',
        'application_deadline',
        'benefits',
        'skills_required',
        'posted_by',
    ];

    protected $casts = [
        'application_deadline' => 'datetime',
        'benefits' => 'array',
        'skills_required' => 'array',
        'is_active' => 'boolean',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
    ];

    const JOB_TYPES = [
        'full-time' => 'Full Time',
        'part-time' => 'Part Time',
        'contract' => 'Contract',
        'freelance' => 'Freelance',
        'internship' => 'Internship',
    ];

    const EXPERIENCE_LEVELS = [
        'entry' => 'Entry Level',
        'mid' => 'Mid Level',
        'senior' => 'Senior Level',
        'executive' => 'Executive Level',
    ];

    const APPLICATION_STATUSES = [
        'pending' => 'Pending',
        'reviewed' => 'Reviewed',
        'shortlisted' => 'Shortlisted',
        'rejected' => 'Rejected',
        'hired' => 'Hired',
    ];

    /**
     * Get the user who posted this job
     */
    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Get all applications for this job
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Get active applications for this job
     */
    public function activeApplications(): HasMany
    {
        return $this->applications()->where('status', '!=', 'rejected');
    }

    /**
     * Check if the job is still accepting applications
     */
    public function isAcceptingApplications(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->application_deadline && now()->isAfter($this->application_deadline)) {
            return false;
        }

        return true;
    }

    /**
     * Get formatted salary range
     */
    public function getFormattedSalaryAttribute(): ?string
    {
        if (!$this->salary_min && !$this->salary_max) {
            return null;
        }

        $min = $this->salary_min ? number_format($this->salary_min) : null;
        $max = $this->salary_max ? number_format($this->salary_max) : null;

        if ($min && $max) {
            return "{$this->currency} {$min} - {$max}";
        } elseif ($min) {
            return "{$this->currency} {$min}+";
        } elseif ($max) {
            return "Up to {$this->currency} {$max}";
        }

        return null;
    }

    /**
     * Scope for active jobs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('application_deadline')
                          ->orWhere('application_deadline', '>', now());
                    });
    }

    /**
     * Scope for jobs by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('job_type', $type);
    }

    /**
     * Scope for jobs by experience level
     */
    public function scopeByExperienceLevel($query, $level)
    {
        return $query->where('experience_level', $level);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $fillable = [
        'job_id',
        'applicant_id',
        'cover_letter',
        'status',
        'resume_data',
        'resume_file',
        'expected_salary',
        'currency',
        'notes',
        'reviewed_at',
        'reviewed_by',
        'calendar_id',
        'task_id',
    ];

    protected $casts = [
        'resume_data' => 'array',
        'reviewed_at' => 'datetime',
        'expected_salary' => 'decimal:2',
    ];

    const STATUSES = [
        'pending' => 'Pending Review',
        'reviewed' => 'Reviewed',
        'shortlisted' => 'Shortlisted',
        'interviewed' => 'Interviewed',
        'rejected' => 'Rejected',
        'hired' => 'Hired',
        'withdrawn' => 'Withdrawn',
    ];

    /**
     * Get the job this application is for
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the applicant who submitted this application
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    /**
     * Get the user who reviewed this application
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the calendar event for this application
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    /**
     * Get the task for this application
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Check if the application can be updated by the applicant
     */
    public function canBeUpdatedByApplicant(): bool
    {
        return in_array($this->status, ['pending', 'reviewed']);
    }

    /**
     * Check if the application can be reviewed by HR/recruiter
     */
    public function canBeReviewed(): bool
    {
        return in_array($this->status, ['pending', 'reviewed', 'shortlisted', 'interviewed']);
    }

    /**
     * Update the application status
     */
    public function updateStatus(string $status, ?int $reviewedBy = null, ?string $notes = null): bool
    {
        $validStatuses = array_keys(self::STATUSES);

        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $updateData = ['status' => $status];

        if ($reviewedBy) {
            $updateData['reviewed_by'] = $reviewedBy;
            $updateData['reviewed_at'] = now();
        }

        if ($notes) {
            $updateData['notes'] = $notes;
        }

        return $this->update($updateData);
    }

    /**
     * Get formatted expected salary
     */
    public function getFormattedExpectedSalaryAttribute(): ?string
    {
        if (!$this->expected_salary) {
            return null;
        }

        return $this->currency . ' ' . number_format($this->expected_salary);
    }

    /**
     * Scope for applications by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for applications by job
     */
    public function scopeForJob($query, $jobId)
    {
        return $query->where('job_id', $jobId);
    }

    /**
     * Scope for applications by applicant
     */
    public function scopeByApplicant($query, $applicantId)
    {
        return $query->where('applicant_id', $applicantId);
    }

    /**
     * Scope for applications reviewed by user
     */
    public function scopeReviewedBy($query, $reviewerId)
    {
        return $query->where('reviewed_by', $reviewerId);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Calendar;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Jobs",
 *     description="API Endpoints for managing job postings and applications"
 * )
 */
class JobController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/jobs",
     *     tags={"Jobs"},
     *     summary="List all active job postings",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         name="job_type",
     *         in="query",
     *         description="Filter by job type",
     *         @OA\Schema(type="string", enum={"full-time", "part-time", "contract", "freelance", "internship"})
     *     ),
     *     @OA\Parameter(
     *         name="experience_level",
     *         in="query",
     *         description="Filter by experience level",
     *         @OA\Schema(type="string", enum={"entry", "mid", "senior", "executive"})
     *     ),
     *     @OA\Parameter(
     *         name="location",
     *         in="query",
     *         description="Filter by location",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jobs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="jobs", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="location", type="string"),
     *                 @OA\Property(property="job_type", type="string"),
     *                 @OA\Property(property="experience_level", type="string"),
     *                 @OA\Property(property="formatted_salary", type="string"),
     *                 @OA\Property(property="department", type="string"),
     *                 @OA\Property(property="application_deadline", type="string", format="date-time"),
     *                 @OA\Property(property="benefits", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="skills_required", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="poster", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             ))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Job::active()->with('poster:id,name');

        if ($request->has('job_type') && $request->job_type) {
            $query->byType($request->job_type);
        }

        if ($request->has('experience_level') && $request->experience_level) {
            $query->byExperienceLevel($request->experience_level);
        }

        if ($request->has('location') && $request->location) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        $jobs = $query->latest()->paginate(20);

        return $this->sendResponse([
            'jobs' => $jobs->items(),
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
            ]
        ], "Jobs retrieved successfully.");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/jobs",
     *     tags={"Jobs"},
     *     summary="Create a new job posting",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","description","requirements"},
     *             @OA\Property(property="title", type="string", example="Senior Laravel Developer"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="requirements", type="string"),
     *             @OA\Property(property="location", type="string", nullable=true),
     *             @OA\Property(property="job_type", type="string", enum={"full-time", "part-time", "contract", "freelance", "internship"}, default="full-time"),
     *             @OA\Property(property="experience_level", type="string", enum={"entry", "mid", "senior", "executive"}, default="entry"),
     *             @OA\Property(property="salary_min", type="number", nullable=true),
     *             @OA\Property(property="salary_max", type="number", nullable=true),
     *             @OA\Property(property="currency", type="string", default="USD"),
     *             @OA\Property(property="department", type="string", nullable=true),
     *             @OA\Property(property="application_deadline", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="benefits", type="array", @OA\Items(type="string"), nullable=true),
     *             @OA\Property(property="skills_required", type="array", @OA\Items(type="string"), nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Job created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="job", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation failed")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'location' => 'nullable|string|max:255',
            'job_type' => ['required', Rule::in(array_keys(Job::JOB_TYPES))],
            'experience_level' => ['required', Rule::in(array_keys(Job::EXPERIENCE_LEVELS))],
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'currency' => 'nullable|string|size:3',
            'department' => 'nullable|string|max:255',
            'application_deadline' => 'nullable|date|after:today',
            'benefits' => 'nullable|array',
            'benefits.*' => 'string',
            'skills_required' => 'nullable|array',
            'skills_required.*' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->sendError("Validation failed", $validator->errors());
        }

        $job = Job::create([
            'title' => $request->title,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'location' => $request->location,
            'job_type' => $request->job_type,
            'experience_level' => $request->experience_level,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'currency' => $request->currency ?? 'USD',
            'department' => $request->department,
            'application_deadline' => $request->application_deadline,
            'benefits' => $request->benefits,
            'skills_required' => $request->skills_required,
            'posted_by' => Auth::id(),
        ]);

        return $this->sendResponse(["job" => $job], "Job created successfully.", 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/jobs/{id}",
     *     tags={"Jobs"},
     *     summary="Get a specific job posting",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Job retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="job", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Job not found")
     * )
     */
    public function show(Job $job)
    {
        $job->load('poster:id,name', 'applications.applicant:id,name,email');

        return $this->sendResponse(["job" => $job], "Job retrieved successfully.");
    }

    /**
     * @OA\Put(
     *     path="/api/v1/jobs/{id}",
     *     tags={"Jobs"},
     *     summary="Update a job posting",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="requirements", type="string"),
     *             @OA\Property(property="location", type="string"),
     *             @OA\Property(property="job_type", type="string"),
     *             @OA\Property(property="experience_level", type="string"),
     *             @OA\Property(property="salary_min", type="number"),
     *             @OA\Property(property="salary_max", type="number"),
     *             @OA\Property(property="currency", type="string"),
     *             @OA\Property(property="department", type="string"),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="application_deadline", type="string", format="date-time"),
     *             @OA\Property(property="benefits", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="skills_required", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Job updated successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Job not found")
     * )
     */
    public function update(Request $request, Job $job)
    {
        if ($job->posted_by !== Auth::id()) {
            return $this->sendError("Unauthorized", ["error" => "You can only update your own job postings."], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'requirements' => 'sometimes|required|string',
            'location' => 'nullable|string|max:255',
            'job_type' => ['sometimes', 'required', Rule::in(array_keys(Job::JOB_TYPES))],
            'experience_level' => ['sometimes', 'required', Rule::in(array_keys(Job::EXPERIENCE_LEVELS))],
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'currency' => 'nullable|string|size:3',
            'department' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'application_deadline' => 'nullable|date|after:today',
            'benefits' => 'nullable|array',
            'benefits.*' => 'string',
            'skills_required' => 'nullable|array',
            'skills_required.*' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->sendError("Validation failed", $validator->errors());
        }

        $job->update($request->validated());

        return $this->sendResponse(["job" => $job], "Job updated successfully.");
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/jobs/{id}",
     *     tags={"Jobs"},
     *     summary="Delete a job posting",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Job deleted successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Job not found")
     * )
     */
    public function destroy(Job $job)
    {
        if ($job->posted_by !== Auth::id()) {
            return $this->sendError("Unauthorized", ["error" => "You can only delete your own job postings."], 403);
        }

        $job->delete();

        return $this->sendResponse(["deleted" => true], "Job deleted successfully.");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/jobs/{job}/apply",
     *     tags={"Jobs"},
     *     summary="Apply for a job",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(name="job", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="cover_letter", type="string"),
     *             @OA\Property(property="expected_salary", type="number", nullable=true),
     *             @OA\Property(property="currency", type="string", default="USD")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Application submitted successfully"),
     *     @OA\Response(response=400, description="Validation failed or already applied"),
     *     @OA\Response(response=404, description="Job not found")
     * )
     */
    public function apply(Request $request, Job $job)
    {
        if (!$job->isAcceptingApplications()) {
            return $this->sendError("Bad request", ["error" => "This job is no longer accepting applications."], 400);
        }

        // Check if user already applied
        $existingApplication = JobApplication::where('job_id', $job->id)
            ->where('applicant_id', Auth::id())
            ->first();

        if ($existingApplication) {
            return $this->sendError("Bad request", ["error" => "You have already applied for this job."], 400);
        }

        $validator = Validator::make($request->all(), [
            'cover_letter' => 'required|string|min:50|max:2000',
            'expected_salary' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
        ]);

        if ($validator->fails()) {
            return $this->sendError("Validation failed", $validator->errors());
        }

        $application = JobApplication::create([
            'job_id' => $job->id,
            'applicant_id' => Auth::id(),
            'cover_letter' => $request->cover_letter,
            'expected_salary' => $request->expected_salary,
            'currency' => $request->currency ?? 'USD',
        ]);

        return $this->sendResponse(["application" => $application], "Application submitted successfully.", 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/jobs/{job}/applications",
     *     tags={"Jobs"},
     *     summary="Get applications for a job (job poster only)",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(name="job", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Applications retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="applications", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="cover_letter", type="string"),
     *                 @OA\Property(property="expected_salary", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="applicant", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="email", type="string")
     *                 )
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Job not found")
     * )
     */
    public function getApplications(Job $job)
    {
        if ($job->posted_by !== Auth::id()) {
            return $this->sendError("Unauthorized", ["error" => "You can only view applications for your own job postings."], 403);
        }

        $applications = $job->applications()->with('applicant:id,name,email')->get();

        return $this->sendResponse(["applications" => $applications], "Applications retrieved successfully.");
    }

    /**
     * @OA\Put(
     *     path="/api/v1/jobs/applications/{application}/status",
     *     tags={"Jobs"},
     *     summary="Update application status (job poster only)",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(name="application", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "reviewed", "shortlisted", "interviewed", "rejected", "hired"}),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Application status updated successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Application not found")
     * )
     */
    public function updateApplicationStatus(Request $request, JobApplication $application)
    {
        if ($application->job->posted_by !== Auth::id()) {
            return $this->sendError("Unauthorized", ["error" => "You can only update applications for your own job postings."], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(array_keys(JobApplication::STATUSES))],
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->sendError("Validation failed", $validator->errors());
        }

        $oldStatus = $application->status;
        $newStatus = $request->status;

        $application->updateStatus($newStatus, Auth::id(), $request->notes);

        // If status changed to 'interviewed', create calendar event and task
        if ($oldStatus !== 'interviewed' && $newStatus === 'interviewed') {
            $this->createInterviewCalendarAndTask($application);
        }

        return $this->sendResponse(["application" => $application->load(['calendar', 'task'])], "Application status updated successfully.");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/my-job-applications",
     *     tags={"Jobs"},
     *     summary="Get current user's job applications",
     *     security={{"Bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Applications retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="applications", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="cover_letter", type="string"),
     *                 @OA\Property(property="expected_salary", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="job", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="location", type="string"),
     *                     @OA\Property(property="job_type", type="string")
     *                 )
     *             ))
     *         )
     *     )
     * )
     */
    public function myApplications()
    {
        $applications = JobApplication::where('applicant_id', Auth::id())
            ->with('job:id,title,location,job_type')
            ->latest()
            ->get();

        return $this->sendResponse(["applications" => $applications], "Your applications retrieved successfully.");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/jobs/applications/{application}/select-interview-date",
     *     tags={"Jobs"},
     *     summary="Select interview date for job application (applicant only)",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(name="application", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"start_time","end_time"},
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2025-09-22T10:00:00Z"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2025-09-22T11:00:00Z"),
     *             @OA\Property(property="location", type="string", nullable=true),
     *             @OA\Property(property="meeting_link", type="string", format="url", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Interview date selected successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Application not found"),
     *     @OA\Response(response=400, description="Application not eligible for interview scheduling")
     * )
     */
    public function selectInterviewDate(Request $request, JobApplication $application)
    {
        if ($application->applicant_id !== Auth::id()) {
            return $this->sendError("Unauthorized", ["error" => "You can only select interview dates for your own applications."], 403);
        }

        if ($application->status !== 'interviewed') {
            return $this->sendError("Bad request", ["error" => "This application is not eligible for interview scheduling."], 400);
        }

        if (!$application->calendar_id) {
            return $this->sendError("Bad request", ["error" => "No calendar event found for this application."], 400);
        }

        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string',
            'meeting_link' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return $this->sendError("Validation failed", $validator->errors());
        }

        $calendar = $application->calendar;

        // Check for conflicts with existing calendar events for the interviewer (job poster)
        $conflicts = Calendar::where('user_id', $application->job->posted_by)
            ->where('id', '!=', $calendar->id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                    });
            })
            ->where('status', 'scheduled')
            ->get();

        if ($conflicts->count() > 0) {
            return $this->sendError("Time conflict", [
                "error" => "The selected time conflicts with existing scheduled events.",
                "conflicts" => $conflicts->map(function ($conflict) {
                    return [
                        'id' => $conflict->id,
                        'title' => $conflict->title,
                        'start_time' => $conflict->start_time,
                        'end_time' => $conflict->end_time,
                    ];
                })
            ], 409);
        }

        $calendar->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'meeting_link' => $request->meeting_link,
        ]);

        return $this->sendResponse(["application" => $application->load(['calendar', 'task'])], "Interview date selected successfully.");
    }

    /**
     * Create calendar event and task for interview
     */
    private function createInterviewCalendarAndTask(JobApplication $application)
    {
        // Create task for interview preparation
        $task = Task::create([
            'title' => "Interview: {$application->job->title} - {$application->applicant->name}",
            'description' => "Conduct interview for {$application->applicant->name} applying for {$application->job->title}",
            'user_id' => $application->job->posted_by,
            'taskable_type' => Job::class,
            'taskable_id' => $application->job_id,
            'status' => Task::NOT_STARTED,
            'priority' => 'high',
        ]);

        // Assign task to job poster (initially)
        $task->assignees()->attach($application->job->posted_by);

        // Create calendar event
        $calendar = Calendar::create([
            'title' => "Interview: {$application->job->title}",
            'description' => "Interview with {$application->applicant->name} for {$application->job->title}",
            'user_id' => $application->job->posted_by,
            'task_id' => $task->id,
            'start_time' => now()->addDays(7), // Default to 1 week from now
            'end_time' => now()->addDays(7)->addHour(),
            'category' => 'meeting',
            'priority' => 'high',
            'status' => 'scheduled',
        ]);

        // Update application with calendar and task IDs
        $application->update([
            'calendar_id' => $calendar->id,
            'task_id' => $task->id,
        ]);
    }
}

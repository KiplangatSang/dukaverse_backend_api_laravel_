<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarRequest;
use App\Http\Requests\UpdateCalendarRequest;
use App\Models\Calendar;
use App\Models\CalendarNotification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Calendar",
 *     description="Manage calendar events"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class CalendarController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/v1/calendars",
     *     operationId="getCalendarEvents",
     *     tags={"Calendar"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get list of calendar events",
     *     description="Returns a list of all calendar events. Can be filtered by user_id, start_date, and end_date.",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date-time")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date-time")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Meeting with Team"),
     *                     @OA\Property(property="start_time", type="string", format="date-time", example="2025-09-22T10:00:00Z"),
     *                     @OA\Property(property="end_time", type="string", format="date-time", example="2025-09-22T11:00:00Z"),
     *                     @OA\Property(property="user_id", type="integer", example=5),
     *                     @OA\Property(property="location", type="string", example="Zoom"),
     *                     @OA\Property(property="description", type="string", example="Monthly planning session.")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Calendar events retrieved successfully")
     *         )
     *     )
     * )
     */

    public function index(Request $request)
    {
        $query = Calendar::with(['task', 'attendees', 'user']);

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        } else {
            $query->where('user_id', Auth::id());
        }

        // Date range filtering
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_time', [$request->start_date, $request->end_date]);
        }

        // Additional filters
        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('upcoming')) {
            $query->upcoming($request->get('days', 7));
        }

        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        if ($request->boolean('recurring')) {
            $query->recurring();
        }

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'start_time');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $events = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $events,
            'message' => 'Calendar events retrieved successfully',
        ]);
    }

/**
 * @OA\Post(
 *     path="/api/v1/calendars",
 *     operationId="createCalendarEvent",
 *     tags={"Calendar"},
 *     security={{"bearerAuth":{}}},
 *     summary="Create a new calendar event",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title", "start_time", "end_time"},
 *             @OA\Property(property="title", type="string", example="Team Meeting"),
 *             @OA\Property(property="start_time", type="string", format="date-time", example="2025-09-22T10:00:00Z"),
 *             @OA\Property(property="end_time", type="string", format="date-time", example="2025-09-22T11:00:00Z"),
 *             @OA\Property(property="user_id", type="integer", example=5),
 *             @OA\Property(property="location", type="string", example="Zoom"),
 *             @OA\Property(property="description", type="string", example="Monthly planning session")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Calendar event created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=10),
 *                 @OA\Property(property="title", type="string", example="Team Meeting"),
 *                 @OA\Property(property="start_time", type="string", format="date-time", example="2025-09-22T10:00:00Z"),
 *                 @OA\Property(property="end_time", type="string", format="date-time", example="2025-09-22T11:00:00Z"),
 *                 @OA\Property(property="user_id", type="integer", example=5),
 *                 @OA\Property(property="location", type="string", example="Zoom"),
 *                 @OA\Property(property="description", type="string", example="Monthly planning session")
 *             ),
 *             @OA\Property(property="message", type="string", example="Calendar event created successfully")
 *         )
 *     )
 * )
 */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'is_all_day' => 'boolean',
            'location' => 'nullable|string',
            'category' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'task_id' => 'nullable|exists:tasks,id',
            'attendees' => 'nullable|array',
            'attendees.*' => 'exists:users,id',
            'recurrence_rule' => 'nullable|array',
            'recurrence_end_date' => 'nullable|date',
            'reminder_settings' => 'nullable|array',
            'reminder_minutes_before' => 'nullable|integer|min:0',
            'meeting_link' => 'nullable|url',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->all();
        $data['user_id'] = Auth::id();

        // Create the calendar event
        $event = Calendar::create($data);

        // Attach attendees if provided
        if ($request->filled('attendees')) {
            $event->attendees()->attach($request->attendees, [
                'role' => 'attendee',
                'status' => 'pending',
                'notify_reminders' => true,
                'notify_updates' => true,
            ]);
        }

        // Create reminder notifications
        if ($event->reminder_minutes_before > 0) {
            CalendarNotification::create([
                'calendar_id' => $event->id,
                'user_id' => $event->user_id,
                'type' => 'reminder',
                'title' => "Reminder: {$event->title}",
                'message' => "You have an upcoming event: {$event->title}",
                'channel' => 'in_app',
                'priority' => $event->priority ?? 'medium',
                'scheduled_at' => $event->start_time->copy()->subMinutes($event->reminder_minutes_before),
                'metadata' => ['event_id' => $event->id],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $event->load(['task', 'attendees', 'user']),
            'message' => 'Calendar event created successfully',
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/calendars/{id}",
     *     operationId="getCalendarEvent",
     *     tags={"Calendar"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get a single calendar event",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the calendar event",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Calendar event retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=10),
     *                 @OA\Property(property="title", type="string", example="Team Meeting"),
     *                 @OA\Property(property="start_time", type="string", format="date-time", example="2025-09-22T10:00:00Z"),
     *                 @OA\Property(property="end_time", type="string", format="date-time", example="2025-09-22T11:00:00Z"),
     *                 @OA\Property(property="user_id", type="integer", example=5),
     *                 @OA\Property(property="location", type="string", example="Zoom"),
     *                 @OA\Property(property="description", type="string", example="Monthly planning session")
     *             ),
     *             @OA\Property(property="message", type="string", example="Calendar event retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Calendar event not found")
     *         )
     *     )
     * )
     */

    public function show(Calendar $calendar)
    {
        return response()->json([
            'success' => true,
            'data'    => $calendar,
            'message' => 'Calendar event retrieved successfully',
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/calendars/{id}",
     *     operationId="updateCalendarEvent",
     *     tags={"Calendar"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update an existing calendar event",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the calendar event",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Meeting Title"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2025-09-23T10:00:00Z"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2025-09-23T11:00:00Z"),
     *             @OA\Property(property="location", type="string", example="Updated Zoom Link"),
     *             @OA\Property(property="description", type="string", example="Updated event description"),
     *             @OA\Property(property="user_id", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Calendar event updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(property="description", type="string", example="Updated event description"),
     *                 @OA\Property(property="user_id", type="integer", example=5)
     *             ),
     *             @OA\Property(property="message", type="string", example="Calendar event updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Calendar event not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Calendar event not found")
     *         )
     *     )
     * )
     */

    public function update(Request $request, Calendar $calendar)
    {
        // Check if user owns this calendar event
        if ($calendar->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this calendar event',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'nullable|date|after:start_time',
            'is_all_day' => 'boolean',
            'location' => 'nullable|string',
            'category' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|in:scheduled,cancelled,completed',
            'task_id' => 'nullable|exists:tasks,id',
            'attendees' => 'nullable|array',
            'attendees.*' => 'exists:users,id',
            'recurrence_rule' => 'nullable|array',
            'recurrence_end_date' => 'nullable|date',
            'reminder_settings' => 'nullable|array',
            'reminder_minutes_before' => 'nullable|integer|min:0',
            'meeting_link' => 'nullable|url',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $calendar->update($request->only([
            'title', 'description', 'start_time', 'end_time', 'is_all_day',
            'location', 'category', 'priority', 'status', 'task_id',
            'recurrence_rule', 'recurrence_end_date', 'reminder_settings',
            'reminder_minutes_before', 'meeting_link', 'notes'
        ]));

        // Update attendees if provided
        if ($request->has('attendees')) {
            $calendar->attendees()->detach();
            if (!empty($request->attendees)) {
                $calendar->attendees()->attach($request->attendees, [
                    'role' => 'attendee',
                    'status' => 'pending',
                    'notify_reminders' => true,
                    'notify_updates' => true,
                ]);
            }
        }

        // Update reminder notifications
        if ($request->has('reminder_minutes_before')) {
            // Remove existing reminders
            CalendarNotification::where('calendar_id', $calendar->id)
                ->where('type', 'reminder')
                ->delete();

            // Create new reminder if needed
            if ($calendar->reminder_minutes_before > 0) {
                CalendarNotification::create([
                    'calendar_id' => $calendar->id,
                    'user_id' => $calendar->user_id,
                    'type' => 'reminder',
                    'title' => "Reminder: {$calendar->title}",
                    'message' => "You have an upcoming event: {$calendar->title}",
                    'channel' => 'in_app',
                    'priority' => $calendar->priority ?? 'medium',
                    'scheduled_at' => $calendar->start_time->copy()->subMinutes($calendar->reminder_minutes_before),
                    'metadata' => ['event_id' => $calendar->id],
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $calendar->load(['task', 'attendees', 'user']),
            'message' => 'Calendar event updated successfully',
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/calendars/{id}",
     *     operationId="deleteCalendarEvent",
     *     tags={"Calendar"},
     *     summary="Delete a calendar event",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the calendar event to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Calendar event deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Calendar event deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Calendar event not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Calendar event not found")
     *         )
     *     )
     * )
     */

    public function destroy(Calendar $calendar)
    {
        // Check if user owns this calendar event
        if ($calendar->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this calendar event',
            ], 403);
        }

        // Delete associated notifications
        $calendar->notifications()->delete();

        // Detach attendees
        $calendar->attendees()->detach();

        $calendar->delete();

        return response()->json([
            'success' => true,
            'message' => 'Calendar event deleted successfully',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/calendars/create-from-task/{task_id}",
     *     operationId="createCalendarFromTask",
     *     tags={"Calendar"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a calendar event from an existing task",
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         required=true,
     *         description="ID of the task",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"start_time", "end_time"},
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2025-09-22T10:00:00Z"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2025-09-22T11:00:00Z"),
     *             @OA\Property(property="location", type="string", example="Office"),
     *             @OA\Property(property="meeting_link", type="string", format="url", example="https://zoom.us/j/123456789"),
     *             @OA\Property(property="reminder_minutes_before", type="integer", example=15)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Calendar event created from task successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Calendar event created from task successfully")
     *         )
     *     )
     * )
     */
    public function createFromTask(Request $request, Task $task)
    {
        // Check if user has access to this task
        if (!$task->assignees()->where('assignee_id', Auth::id())->exists() && $task->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create calendar event for this task',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'location' => 'nullable|string',
            'meeting_link' => 'nullable|url',
            'reminder_minutes_before' => 'nullable|integer|min:0',
            'attendees' => 'nullable|array',
            'attendees.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check for conflicts
        $conflicts = Calendar::where('user_id', Auth::id())
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
            return response()->json([
                'success' => false,
                'message' => 'Time conflict detected',
                'errors' => [
                    'conflicts' => $conflicts->map(function ($conflict) {
                        return [
                            'id' => $conflict->id,
                            'title' => $conflict->title,
                            'start_time' => $conflict->start_time,
                            'end_time' => $conflict->end_time,
                        ];
                    })
                ]
            ], 409);
        }

        // Create calendar event
        $event = Calendar::create([
            'title' => $task->title,
            'description' => $task->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'user_id' => Auth::id(),
            'task_id' => $task->id,
            'location' => $request->location,
            'meeting_link' => $request->meeting_link,
            'priority' => $this->mapTaskPriorityToCalendar($task->priority ?? 'medium'),
            'category' => 'task',
            'reminder_minutes_before' => $request->reminder_minutes_before ?? 15,
            'status' => 'scheduled',
        ]);

        // Attach task assignees as attendees
        $attendees = $task->assignees()->pluck('assignee_id')->toArray();
        if ($request->filled('attendees')) {
            $attendees = array_unique(array_merge($attendees, $request->attendees));
        }

        if (!empty($attendees)) {
            $event->attendees()->attach($attendees, [
                'role' => 'attendee',
                'status' => 'pending',
                'notify_reminders' => true,
                'notify_updates' => true,
            ]);
        }

        // Create reminder notification
        if ($event->reminder_minutes_before > 0) {
            CalendarNotification::create([
                'calendar_id' => $event->id,
                'user_id' => $event->user_id,
                'type' => 'reminder',
                'title' => "Reminder: {$event->title}",
                'message' => "You have an upcoming task event: {$event->title}",
                'channel' => 'in_app',
                'priority' => $event->priority ?? 'medium',
                'scheduled_at' => $event->start_time->copy()->subMinutes($event->reminder_minutes_before),
                'metadata' => ['event_id' => $event->id, 'task_id' => $task->id],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $event->load(['task', 'attendees', 'user']),
            'message' => 'Calendar event created from task successfully',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/calendars/{calendar}/reschedule",
     *     operationId="rescheduleCalendarEvent",
     *     tags={"Calendar"},
     *     security={{"bearerAuth":{}}},
     *     summary="Reschedule a calendar event (drag-and-drop functionality)",
     *     @OA\Parameter(
     *         name="calendar",
     *         in="path",
     *         required=true,
     *         description="ID of the calendar event",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"start_time", "end_time"},
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2025-09-22T14:00:00Z"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2025-09-22T15:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Calendar event rescheduled successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Calendar event rescheduled successfully")
     *         )
     *     )
     * )
     */
    public function reschedule(Request $request, Calendar $calendar)
    {
        if ($calendar->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to reschedule this calendar event',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check for conflicts (excluding current event)
        $conflicts = Calendar::where('user_id', Auth::id())
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
            return response()->json([
                'success' => false,
                'message' => 'Time conflict detected',
                'errors' => [
                    'conflicts' => $conflicts->map(function ($conflict) {
                        return [
                            'id' => $conflict->id,
                            'title' => $conflict->title,
                            'start_time' => $conflict->start_time,
                            'end_time' => $conflict->end_time,
                        ];
                    })
                ]
            ], 409);
        }

        $calendar->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        // Update reminder notifications
        if ($calendar->reminder_minutes_before > 0) {
            CalendarNotification::where('calendar_id', $calendar->id)
                ->where('type', 'reminder')
                ->update([
                    'scheduled_at' => $calendar->start_time->copy()->subMinutes($calendar->reminder_minutes_before)
                ]);
        }

        return response()->json([
            'success' => true,
            'data' => $calendar->load(['task', 'attendees', 'user']),
            'message' => 'Calendar event rescheduled successfully',
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/calendars/{calendar}/resize",
     *     operationId="resizeCalendarEvent",
     *     tags={"Calendar"},
     *     security={{"bearerAuth":{}}},
     *     summary="Resize a calendar event duration (drag-and-drop functionality)",
     *     @OA\Parameter(
     *         name="calendar",
     *         in="path",
     *         required=true,
     *         description="ID of the calendar event",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"end_time"},
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2025-09-22T12:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Calendar event resized successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Calendar event resized successfully")
     *         )
     *     )
     * )
     */
    public function resize(Request $request, Calendar $calendar)
    {
        if ($calendar->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to resize this calendar event',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'end_time' => 'required|date|after:' . $calendar->start_time,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $calendar->update([
            'end_time' => $request->end_time,
        ]);

        return response()->json([
            'success' => true,
            'data' => $calendar->load(['task', 'attendees', 'user']),
            'message' => 'Calendar event resized successfully',
        ]);
    }

    // Helper Methods
    private function mapTaskPriorityToCalendar($taskPriority)
    {
        return match($taskPriority) {
            'low' => 'low',
            'medium' => 'medium',
            'high' => 'high',
            default => 'medium',
        };
    }
}

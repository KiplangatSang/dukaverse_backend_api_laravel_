<?php
namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDependancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        $tasks = $this->account()
            ->tasks()
            ->with('assignees')
            ->with('dependencies')
            ->with('taskable')
            ->with("comments")
            ->get();

        if (! $tasks) {
            return $this->sendError("Bad request", ["error" => "The tasks could not be fetched.", "result" => $tasks]);
        }

        return $this->sendResponse(["tasks" => $tasks], "success, The tasks has been fetched successfully.");

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

        $task_priorities = Task::TASK_PRIORITIES;
        $projects        = $this->account()->projects()->with('teams.members')->with('tasks')->get();
        $campaigns       = $this->account()->projects()->with('teams.members')->with('tasks')->get();

        $task_dependencies = TaskDependancy::TASK_DEPENDENCIES;

        if (! $task_priorities) {
            return $this->sendError("Bad request", ["error" => "The task fields could not be fetched.", "result" => $task_priorities]);
        }

        return $this->sendResponse(["task_priorities" => $task_priorities,
            "projects"                                    => $projects,
            "campaigns"                                   => $campaigns,
            "task_dependencies"                           => $task_dependencies], "success, The task fields has been fetched successfully.");

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $validator = Validator::make($request->all(),
            [
                "user_id"      => ['sometimes', 'exists:users,id'],
                "name"         => ['required'],
                "start_date"   => ['required'],
                "end_date"     => ['required'],
                "progress"     => ['required'],
                "dependencies" => ['sometimes'],
                "priority"     => ['required'],
            ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The task could not be saved .", "result" => $validator->errors()]);
        }

        $validatated = $validator->validated();

        $task = null;
        if ($request->project_id) {
            $task = $this->account()->tasks()->create(
                [
                    "taskable_id"   => $request->project_id,
                    "taskable_type" => Project::class,
                    "user_id"       =>Auth::id(),
                    "name"          => $validatated["name"],
                    "start_date"    => $validatated["start_date"],
                    "end_date"      => $validatated["end_date"],
                    "progress"      => $validatated["progress"],
                    "priority"      => $validatated["priority"],

                ]
            );

        }
        if ($request->campaign_id) {
            $task = $this->account()->tasks()->create(
                [
                    "taskable_id"   => $request->campaign_id,
                    "taskable_type" => Campaign::class,
                    "user_id"       =>Auth::id(),
                    "name"          => $validatated["name"],
                    "start_date"    => $validatated["start_date"],
                    "end_date"      => $validatated["end_date"],
                    "progress"      => $validatated["progress"],
                    "priority"      => $validatated["priority"],

                ]
            );

        }

        $dependencyErrors = [];
        if ($request->dependencies) {
            $dependencies = json_decode($request->dependencies);
            foreach ($dependencies as $dependency) {

                $validator = Validator::make([$dependency->dependency => "task_dependency"],
                    [
                        "task_dependency" => ['exists:tasks,id'],
                    ]);

                if ($validator->fails()) {
                    return $validator->errors();
                    array_push($dependencyErrors, $validator->errors());
                }

                $created_task_dependency = $task->dependencies()->create(
                    ["taskable_id"    => $task->taskable_id,
                        "taskable_type"   => $task->taskable_type,
                        "project_id"      => $request->project_id,
                        "depends_on"      => $dependency->dependency,
                        "dependency_type" => $dependency->key]
                );

            }

        }

        $task = Task::where('id', $task->id)->with('project')->with('dependencies')->first();

        if (! $task) {
            return $this->sendError("Bad request", ["error" => "The  task could not be saved.", "result" => $task, "dependencyErrors" => $dependencyErrors]);
        }

        return $this->sendResponse(["task" => $task, "dependencyErrors" => $dependencyErrors], "success, The  task has been saved successfully.");

    }

    Route::post('tasks/{task_id}/comments', [TaskController::class, 'addComment']);
        Route::put('tasks/{task_id}/comments/{comment_id}', [TaskController::class, 'updateComment']);
        Route::delete('tasks/{task_id}/comments/{comment_id}', [TaskController::class, 'deleteComment']);

public function addComment(Request $request, $task_id){

}
public function updateComment(Request $request, $task_id){

}
public function deleteComment(Request $request, $task_id){

}
    /**
     * Store subtasks.
     */
    public function storeSubTasks(Request $request)
    {
        //

        $validator = Validator::make($request->all(),
            [
                "task_id" => ['required', 'exists:projects,id'],
                "user_id" => ['sometimes', 'exists:users,id'],
                "name"    => ['required'],
            ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The task could not be saved .", "result" => $validator->errors()]);
        }

        $validatated = $validator->validated();
        $parent_task = Task::where('id', $request->task_id)->first();

        $task = $this->account()->tasks()->create(
            [
                "parent_id"      => $request->task_id,
                "ownerable_id"   => $parent_task->ownerable_id,
                "ownerable_type" => $parent_task->ownerable_type,
                "taskable_id"    => $parent_task->taskable_id,
                "taskable_type"  => $parent_task->taskable_type,
                "user_id"        => $request->user_id ?? Auth::id(),
                "name"           => $validatated["name"],
            ]
        );

        $dependencyErrors = [];
        if ($request->dependencies) {
            $dependencies = json_decode($request->dependencies);
            foreach ($dependencies as $dependency) {

                $validator = Validator::make([$dependency->dependency => "task_dependency"],
                    [
                        "task_dependency" => ['exists:tasks,id'],
                    ]);

                if ($validator->fails()) {
                    return $validator->errors();
                    array_push($dependencyErrors, $validator->errors());
                }

                $created_task_dependency = $task->dependencies()->create(
                    ["taskable_id"    => $task->taskable_id,
                        "taskable_type"   => $task->taskable_type,
                        "taskable_id"     => $request->project_id,
                        "depends_on"      => $dependency->dependency,
                        "dependency_type" => $dependency->key]
                );

            }

        }

        $task = Task::where('id', $task->id)->with('project')->with('dependencies')->first();

        if (! $task) {
            return $this->sendError("Bad request", ["error" => "The  task could not be saved.", "result" => $task, "dependencyErrors" => $dependencyErrors]);
        }

        return $this->sendResponse(["task" => $task, "dependencyErrors" => $dependencyErrors], "success, The  task has been saved successfully.");

    }

    /**
     * Display the specified resource.
     */
    public function show($task)
    {
        //

        $task = $this->account()->tasks()->where('id', $task)
            ->with('assignees')
            ->with('dependencies.task')
            ->with('dependencies.dependedTask')
            ->with('taskable')
            ->with('subTasks')
            ->with('comments.user')
            ->with("comments.replies.user")
            ->first();

        if (! $task) {
            return $this->sendError("Bad request", ["error" => "The   task could not be found.", "result" => $task]);
        }

        return $this->sendResponse(["task" => $task], "success, The task has been fetched successfully.");

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //

        $task_priorities = Task::TASK_PRIORITIES;
        if (! $task_priorities) {
            return $this->sendError("Bad request", ["error" => "The task fields could not be fetched.", "result" => $task_priorities]);
        }

        $task = $this->account()->tasks()->where('id', $task)->first();

        if (! $task) {
            return $this->sendError("Bad request", ["error" => "The   task could not be found.", "result" => $task]);
        }

        return $this->sendResponse(["task" => $task, "task_priorities" => $task_priorities], "success, The task has been fetched successfully.");

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $task)
    {
        //

        $validator = Validator::make($request->all(),
            [
                "project_id"   => ['sometimes', "exists:projects,id"],
                "campaign_id"  => ['sometimes', "exists:campaigns,id"],
                "name"         => ['sometimes', "string"],
                "start_date"   => ['sometimes'],
                "end_date"     => ['sometimes'],
                "progress"     => ['sometimes'],
                "dependencies" => ['sometimes'],
                "priority"     => ['sometimes'],
            ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The task could not be saved .", "result" => $validator->errors()]);
        }

        $validatated = $validator->validated();

        $task = $this->account()->tasks()->where('id', $task)->first();

        if (! $task) {
            return $this->sendError("Bad request", ["error" => "The   task could not be found.", "result" => $task]);
        }

        if ($request->status) {
            $result = $task->update(
                [

                    "status" => $request->status,
                ]
            );

        }
        $result = $task->update(
            [
                "name"         => $request->name ?? $task->name,
                "start_date"   => $request->start_date ?? $task->start_date,
                "end_date"     => $request->end_date ?? $task->end_date,
                "progress"     => $request->progress ?? $task->progress,
                "dependencies" => $request->dependencies ?? $task->dependencies,
                "priority"     => $request->priority ?? $task->priority,
                "status"       => $request->status ?? $task->status,
            ]
        );

        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The  task could not be updated.", "result" => $result]);
        }

        $task = $this->account()->tasks()->where('id', $task->id)->first();

        return $this->sendResponse(["task" => $task], "success, The  task has been updated successfully.");

    }

    /**
     * Assign task to the team members.
     */
    public function assignTask(Request $request, $task)
    {
        //

        $validator = Validator::make($request->all(),
            [
                "assignees" => ['required'],
            ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The task could not be saved .", "result" => $validator->errors()]);
        }

        $task = $this->account()->tasks()->where('id', $task)->first();

        if (! $task) {
            return $this->sendError("Bad request", ["error" => "The   task could not be found.", "result" => $task]);
        }

        $assignees = json_decode($request->assignees);
        $result    = $task->assignees()->syncWithoutDetaching($assignees);

        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The  task could not be updated.", "result" => $result]);
        }

        $task = $this->account()->tasks()->where('id', $task)->first();

        return $this->sendResponse(["task" => $task], "success, The  task has been updated successfully.");

    }

    /**
     * @OA\Post(
     *     path="/api/v1/tasks/{taskId}/convert-to-todo",
     *     operationId="convertTaskToTodo",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     summary="Convert a task to a todo",
     *     description="Converts a task to a single todo. If task has subtasks, returns information about subtasks for user to choose conversion method.",
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         description="ID of the task to convert",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="todo_type", type="string", enum={"account", "user"}, default="account", description="Type of todo to create")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task converted to todo successfully or has subtasks",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="success", type="boolean", example=true),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="has_subtasks", type="boolean", example=true),
     *                         @OA\Property(property="subtasks_count", type="integer", example=3),
     *                         @OA\Property(property="message", type="string", example="Task has subtasks. Please choose how to proceed.")
     *                     ),
     *                     @OA\Property(property="message", type="string", example="Task has subtasks - choose conversion method")
     *                 ),
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="success", type="boolean", example=true),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="todo", type="object")
     *                     ),
     *                     @OA\Property(property="message", type="string", example="Task converted to todo successfully")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task not found"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function convertToTodo(Request $request, $taskId)
    {
        $task = $this->account()->tasks()->where('id', $taskId)->first();

        if (!$task) {
            return $this->sendError("Task not found", ["errors" => "Task does not exist"]);
        }

        // Check if task has subtasks
        $hasSubtasks = $task->subTasks()->count() > 0;

        if ($hasSubtasks) {
            return $this->sendResponse([
                "has_subtasks" => true,
                "subtasks_count" => $task->subTasks()->count(),
                "message" => "Task has subtasks. Please choose how to proceed."
            ], "Task has subtasks - choose conversion method");
        }

        // If no subtasks, create a single todo from the task
        $todoType = $request->input('todo_type', \App\Models\Todo::ACCOUNT_TODO_TYPES);
        $account = $this->user();

        if ($todoType == \App\Models\Todo::ACCOUNT_TODO_TYPES) {
            $account = $this->account();
        }

        $todo = $account->todos()->create([
            "todo" => "Complete task: {$task->name}",
            "note" => "Converted from task - Priority: {$task->priority}, Due: {$task->end_date}",
            "project_id" => $task->taskable_type === \App\Models\Project::class ? $task->taskable_id : null,
            "user_id" =>Auth::id(),
            "assigned_to" => $task->assignees->first()?->id ??Auth::id(),
        ]);

        return $this->sendResponse(["todo" => $todo], "Task converted to todo successfully");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tasks/{taskId}/break-into-todos",
     *     operationId="breakTaskIntoTodos",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     summary="Break down a task into multiple todos",
     *     description="Breaks down a task into multiple todos based on provided breakdown structure",
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         description="ID of the task to break down",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"breakdown"},
     *             @OA\Property(
     *                 property="breakdown",
     *                 type="array",
     *                 description="Array of todo items to create",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"title"},
     *                     @OA\Property(property="title", type="string", example="Research phase"),
     *                     @OA\Property(property="description", type="string", example="Gather requirements and research"),
     *                     @OA\Property(property="assigned_to", type="integer", example=1, description="User ID to assign the todo to")
     *                 )
     *             ),
     *             @OA\Property(property="todo_type", type="string", enum={"account", "user"}, default="account", description="Type of todo to create")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task broken into todos successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="todos", type="array", @OA\Items(type="object"))
     *             ),
     *             @OA\Property(property="message", type="string", example="Task broken into todos successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task not found"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function breakIntoTodos(Request $request, $taskId)
    {
        $task = $this->account()->tasks()->where('id', $taskId)->first();

        if (!$task) {
            return $this->sendError("Task not found", ["errors" => "Task does not exist"]);
        }

        $todoBreakdown = $request->input('breakdown', []);
        $todoType = $request->input('todo_type', \App\Models\Todo::ACCOUNT_TODO_TYPES);
        $account = $this->user();

        if ($todoType == \App\Models\Todo::ACCOUNT_TODO_TYPES) {
            $account = $this->account();
        }

        $createdTodos = [];
        foreach ($todoBreakdown as $todoItem) {
            $todo = $account->todos()->create([
                "todo" => $todoItem['title'],
                "note" => "Part of task: {$task->name} - {$todoItem['description']}",
                "project_id" => $task->taskable_type === \App\Models\Project::class ? $task->taskable_id : null,
                "user_id" =>Auth::id(),
                "assigned_to" => $todoItem['assigned_to'] ?? ($task->assignees->first()?->id ??Auth::id()),
            ]);
            $createdTodos[] = $todo;
        }

        return $this->sendResponse(["todos" => $createdTodos], "Task broken into todos successfully");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tasks/{taskId}/convert-subtasks-to-todos",
     *     operationId="convertSubtasksToTodos",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     summary="Convert task subtasks to todos",
     *     description="Converts all subtasks of a given task into individual todos",
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         description="ID of the task whose subtasks to convert",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="todo_type", type="string", enum={"account", "user"}, default="account", description="Type of todo to create")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subtasks converted to todos successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="todos", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="converted_count", type="integer", example=3)
     *             ),
     *             @OA\Property(property="message", type="string", example="Subtasks converted to todos successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found or no subtasks found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task not found"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function convertSubtasksToTodos(Request $request, $taskId)
    {
        $task = $this->account()->tasks()->where('id', $taskId)->first();

        if (!$task) {
            return $this->sendError("Task not found", ["errors" => "Task does not exist"]);
        }

        $subtasks = $task->subTasks;
        if ($subtasks->isEmpty()) {
            return $this->sendError("No subtasks found", ["errors" => "Task has no subtasks to convert"]);
        }

        $todoType = $request->input('todo_type', \App\Models\Todo::ACCOUNT_TODO_TYPES);
        $account = $this->user();

        if ($todoType == \App\Models\Todo::ACCOUNT_TODO_TYPES) {
            $account = $this->account();
        }

        $createdTodos = [];
        foreach ($subtasks as $subtask) {
            $todo = $account->todos()->create([
                "todo" => "Complete subtask: {$subtask->name}",
                "note" => "Subtask of: {$task->name} - Priority: {$subtask->priority}, Due: {$subtask->end_date}",
                "project_id" => $task->taskable_type === \App\Models\Project::class ? $task->taskable_id : null,
                "user_id" =>Auth::id(),
                "assigned_to" => $subtask->assignees->first()?->id ?? $subtask->user_id ??Auth::id(),
            ]);
            $createdTodos[] = $todo;
        }

        return $this->sendResponse([
            "todos" => $createdTodos,
            "converted_count" => count($createdTodos)
        ], "Subtasks converted to todos successfully");
    }

    // Temporarily disable Swagger annotation for createCalendarFromTask to resolve merge error
    /*
    @OA\Post(
        path="/api/v1/calendars/create-from-task/{task_id}",
        operationId="createCalendarFromTask",
        tags={"Calendar"},
        security={{"bearerAuth":{}}},
        summary="Create a calendar event from an existing task",
        description="Creates a calendar event from a task with automatic attendee management and conflict detection",
        @OA\Parameter(
            name="task_id",
            in="path",
            required=true,
            description="ID of the task to create calendar event from",
            @OA\Schema(type="integer")
        ),
        @OA\RequestBody(
            required=true,
            @OA\JsonContent(
                required={"start_time", "end_time"},
                @OA\Property(property="start_time", type="string", format="date-time", example="2025-09-22T10:00:00Z"),
                @OA\Property(property="end_time", type="string", format="date-time", example="2025-09-22T11:00:00Z"),
                @OA\Property(property="location", type="string", example="Office"),
                @OA\Property(property="meeting_link", type="string", format="url", example="https://zoom.us/j/123456789"),
                @OA\Property(property="reminder_minutes_before", type="integer", example=15),
                @OA\Property(property="attendees", type="array", @OA\Items(type="integer"), description="Additional attendee user IDs")
            )
        ),
        @OA\Response(
            response=201,
            description="Calendar event created from task successfully",
            @OA\JsonContent(
                type="object",
                @OA\Property(property="success", type="boolean", example=true),
                @OA\Property(property="data", type="object"),
                @OA\Property(property="message", type="string", example="Calendar event created from task successfully")
            )
        ),
        @OA\Response(
            response=403,
            description="Unauthorized to create calendar event for this task",
            @OA\JsonContent(
                type="object",
                @OA\Property(property="success", type="boolean", example=false),
                @OA\Property(property="message", type="string", example="Unauthorized to create calendar event for this task")
            )
        ),
        @OA\Response(
            response=409,
            description="Time conflict detected",
            @OA\JsonContent(
                type="object",
                @OA\Property(property="success", type="boolean", example=false),
                @OA\Property(property="message", type="string", example="Time conflict detected"),
                @OA\Property(property="errors", type="object", @OA\Property(property="conflicts", type="array"))
            )
        )
    )
    */
    public function createCalendarFromTask(Request $request, $taskId)
    {
        // This method should be in CalendarController, but adding swagger docs here for completeness
        // The actual implementation is in CalendarController.php
        return response()->json([
            'success' => false,
            'message' => 'This endpoint is implemented in CalendarController'
        ], 501);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($task)
    {
        //
        $result = Task::destroy($task);
        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The  task could not be deleted.", "result" => $result]);
        }

        return $this->sendResponse(["result" => $result], "success, The  task has been deleted successfully.");

    }
}

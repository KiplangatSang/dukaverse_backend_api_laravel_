<?php
namespace App\Services;

use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;
use App\Models\Campaign;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDependancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TasksService extends BaseService
{
    //

    public function __construct(
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }


    /**
     * Display a listing of the resource.
     */
    public function listTasks()
    {
        //

        $tasks = $this->account
            ->tasks()
            ->with('assignees')
            ->with('dependencies')
            ->with('taskable')
            ->with("comments")
            ->get();

        if (! $tasks) {
            return $this->response("Bad request", ["error" => "The tasks could not be fetched.", "result" => $tasks]);
        }

        return $this->sendResponse(["tasks" => $tasks], "success, The tasks has been fetched successfully.");

    }

     private function taskPriorities()
    {
     $task_priorities = Task::TASK_PRIORITIES;

     return $task_priorities;
    }


    private function taskTeamMembers(int $taskId)
    {
     $teamMembers = Task::where('id', $taskId)->project()->members()->flatten()->unique('id')->values();

     return $teamMembers;
    }


    private function taskProject(int $taskId)
    {
     $task_priorities = Task::where('id', $taskId)->project()->first();

     return $task_priorities;
    }

    /**
     * Create task objects.
     */

    public function createTaskObjects()
    {
        //

        $task_priorities = $this->taskPriorities();
        $projects        = $this->account->projects()->with('teams.members')->with('tasks')->get();
        $campaigns       = $this->account->projects()->with('teams.members')->with('tasks')->get();

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
            $task = $this->account->tasks()->create(
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
            $task = $this->account->tasks()->create(
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

    // Route::post('tasks/{task_id}/comments', [TaskController::class, 'addComment']);
    //     Route::put('tasks/{task_id}/comments/{comment_id}', [TaskController::class, 'updateComment']);
    //     Route::delete('tasks/{task_id}/comments/{comment_id}', [TaskController::class, 'deleteComment']);

public function addComment(Request  $request, $task_id){
$validator = Validator::make($request->all(), [
            "commentable_type" => "required",
            "commentable_id"   => "required",
            "content"          => ["required", "string"],
        ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The comment could not be saved.", "result" => $validator->errors()]);
        }

        $validatated = $validator->validated();

        $comment = $this->user()->comments()->create([
            "parent_id"        => $request->parent_comment_id,
            "commentable_type" => $request->commentable_type ? Comment::COMMENTABLE_TYPES[$request->commentable_type] : null,
            "commentable_id"   => $validatated['commentable_id'],
            "content"          => $validatated['content'],
            "user_id"          => Auth::id(),
        ]);

        if (! $comment) {
            return $this->sendError("Bad request", ["error" => "The comment could not be saved.", "result" => $comment]);
        }

        return $this->sendResponse(["comment" => $comment], "success, The comment has been saved successfully.");
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

        $task = $this->account->tasks()->create(
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

        $task = $this->account->tasks()->where('id', $task)
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

        $task = $this->account->tasks()->where('id', $task)->first();

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

        $task = $this->account->tasks()->where('id', $task)->first();

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

        $task = $this->account->tasks()->where('id', $task->id)->first();

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

        $task = $this->account->tasks()->where('id', $task)->first();

        if (! $task) {
            return $this->sendError("Bad request", ["error" => "The   task could not be found.", "result" => $task]);
        }

        $assignees = json_decode($request->assignees);
        $result    = $task->assignees()->syncWithoutDetaching($assignees);

        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The  task could not be updated.", "result" => $result]);
        }

        $task = $this->account->tasks()->where('id', $task)->first();

        return $this->sendResponse(["task" => $task], "success, The  task has been updated successfully.");

    }


    public function convertToTodo(Request $request, $taskId)
    {
        $task = $this->account->tasks()->where('id', $taskId)->first();

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
            $account = $this->account;
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


    public function breakIntoTodos(Request $request, $taskId)
    {
        $task = $this->account->tasks()->where('id', $taskId)->first();

        if (!$task) {
            return $this->sendError("Task not found", ["errors" => "Task does not exist"]);
        }

        $todoBreakdown = $request->input('breakdown', []);
        $todoType = $request->input('todo_type', \App\Models\Todo::ACCOUNT_TODO_TYPES);
        $account = $this->user();

        if ($todoType == \App\Models\Todo::ACCOUNT_TODO_TYPES) {
            $account = $this->account;
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

    public function convertSubtasksToTodos(Request $request, $taskId)
    {
        $task = $this->account->tasks()->where('id', $taskId)->first();

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
            $account = $this->account;
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


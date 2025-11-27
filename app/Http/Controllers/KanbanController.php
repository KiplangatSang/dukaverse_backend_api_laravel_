<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Kanban",
 *     description="Endpoints for managing Kanban boards and tasks"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class KanbanController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/kanban/{project_id}",
     *     tags={"Kanban"},
     *     security={{"bearerAuth":{}}},
     *     summary="Fetch Kanban columns and tasks for a project (or all tasks if no project)",
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         required=true,
     *         description="Project ID (if null, fetches tasks for the account)",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kanban columns and tasks retrieved successfully"
     *     ),
     *     @OA\Response(response=404, description="Project not found")
     * )
     */
    public function index($project_id)
    {
        $tasks   = null;
        $project = null;

        if ($project_id) {
            $project = Project::where('id', $project_id)
                ->with('tasks.comments')
                ->with('tasks.assignees')
                ->first();

            if ($project) {
                $tasks = $project->tasks;
            }
        }

        if (! $project) {
            $tasks = $this->account()->tasks()->with(['comments', 'assignees'])->get();
        }

        $kanban_columns = Task::KANBAN_COLUMNS;

        foreach ($kanban_columns as $key => $column) {
            $column_tasks = [];
            if ($tasks) {
                $column_tasks = $tasks->where('status', $column['title']);
            }

            $column['tasks']      = $column_tasks;
            $kanban_columns[$key] = $column;
        }

        return $this->sendResponse([
            "kanban_columns" => $kanban_columns,
            "project"        => $project,
            "tasks"          => $tasks,
        ], "Data fetched successfully");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/kanban/{project_id}/update",
     *     tags={"Kanban"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update Kanban board task statuses for a project",
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         required=true,
     *         description="Project ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="tasks",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="status", type="string", example="In Progress")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Tasks updated successfully"),
     *     @OA\Response(response=400, description="Invalid request payload")
     * )
     */
    public function update(Request $request, string $project_id)
    {
        $tasks = $request->input('tasks');

        foreach ($tasks as $taskData) {
            $task = Task::find($taskData['id']);
            if ($task) {
                $task->status = $taskData['status'];
                $task->save();
            }
        }

        $project        = Project::where('id', $project_id)->with('tasks')->first();
        $kanban_columns = Task::KANBAN_COLUMNS;

        foreach ($kanban_columns as $key => $column) {
            $tasks                = $project->tasks()->where('status', $column['title'])->with(['comments', 'assignees'])->get();
            $column['tasks']      = $tasks;
            $kanban_columns[$key] = $column;
        }

        return $this->sendResponse([
            'message'        => 'Tasks updated successfully',
            "kanban_columns" => $kanban_columns,
            "project"        => $project,
        ], "Data updated successfully");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/kanban/update-board",
     *     tags={"Kanban"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update Kanban board task statuses for account-wide tasks",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="tasks",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="status", type="string", example="Completed")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Tasks updated successfully"),
     *     @OA\Response(response=400, description="Invalid request payload")
     * )
     */
    public function updateKanbanboard(Request $request, $project_id = null)
    {
        $tasks = $request->input('tasks');

        foreach ($tasks as $taskData) {
            $task = Task::find($taskData['id']);
            if ($task) {
                $task->status = $taskData['status'];
                $task->save();
            }
        }

        $project = null;
        if ($project_id) {
            $project = Project::where('id', $project_id)->with('tasks')->first();
        }

        $kanban_columns = Task::KANBAN_COLUMNS;

        foreach ($kanban_columns as $key => $column) {
            $tasks                = $this->account()->tasks()->where('status', $column['title'])->with(['comments', 'assignees'])->get();
            $column['tasks']      = $tasks;
            $kanban_columns[$key] = $column;
        }

        return $this->sendResponse([
            'message'        => 'Tasks updated successfully',
            "kanban_columns" => $kanban_columns,
            "project"        => $project,
        ], "Data updated successfully");
    }
}

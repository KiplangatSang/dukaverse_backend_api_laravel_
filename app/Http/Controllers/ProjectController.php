<?php
namespace App\Http\Controllers;

use App\Http\Requests\UpdateProjectRequest;
use App\Models\Account;
use App\Models\Comment;
use App\Models\Project;
use App\Models\TaskDependancy;
use App\Models\Team;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Projects",
 *     description="API Endpoints for managing Projects"
 * )
 */
class ProjectController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/projects",
     *     tags={"Projects"},
     *     summary="List all projects for the authenticated account",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Projects fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="projects", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function index()
    {
        $projects = $this->account()->projects()
            ->with('tasks.dependencies', 'tasks.comments', 'tasks.assignees', 'tasks.media', 'teams.members', 'comments')
            ->get();

        if (! $projects) {
            return $this->sendError("Bad request", ["error" => "The projects could not be fetched."]);
        }

        return $this->sendResponse(["projects" => $projects], "success, The projects have been fetched successfully.");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/projects/create",
     *     tags={"Projects"},
     *     summary="Fetch defaults for creating a new project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Defaults fetched successfully"),
     * )
     */
    public function create()
    {
        $project_defaults  = Project::PROJECT_DEFAULTS;
        $task_dependencies = TaskDependancy::TASK_DEPENDENCIES;
        $team_members      = $this->accountMembers();

        return $this->sendResponse([
            "project_defaults"  => $project_defaults,
            "team_members"      => $team_members,
            "task_dependencies" => $task_dependencies,
        ], "success, The projects data have been fetched successfully.");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/projects",
     *     tags={"Projects"},
     *     summary="Create a new project",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","overview","start_date","due_date","budget"},
     *             @OA\Property(property="name", type="string", example="Website Redesign"),
     *             @OA\Property(property="overview", type="string"),
     *             @OA\Property(property="start_date", type="string", example="2025-09-01 10:00:00"),
     *             @OA\Property(property="due_date", type="string", example="2025-12-01 17:00:00"),
     *             @OA\Property(property="budget", type="number"),
     *             @OA\Property(property="team", type="string", nullable=true),
     *             @OA\Property(property="teamMembers", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Project created successfully"),
     *     @OA\Response(response=400, description="Validation failed")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name"        => ['required', 'string', 'unique:projects'],
            "overview"    => ['required', 'string'],
            "start_date"  => ['required', 'string'],
            "due_date"    => ['required', 'string'],
            "budget"      => ['required'],
            "avatar"      => ['sometimes', 'file'],
            "teamMembers" => ['sometimes', 'string'],
            "team"        => ['sometimes', 'string', 'exists:teams,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", $validator->errors());
        }

        $validated      = $validator->validated();
        $project_avatar = $this->saveFile("projects", $request->avatar);

        try {
            $startDate               = new DateTime($validated["start_date"]);
            $validated["start_date"] = $startDate->format('Y-m-d H:i:s');
            $dueDate                 = new DateTime($validated["due_date"]);
            $validated["due_date"]   = $dueDate->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            info(["Invalid date provided: " => $e->getMessage()]);
        }

        $account = $this->account();

        $project = $account->projects()->updateOrCreate(
            ['user_id' => Auth::id(), 'name' => $validated['name']],
            [
                "overview"   => $validated["overview"],
                "start_date" => $validated["start_date"],
                "due_date"   => $validated["due_date"],
                "budget"     => $validated["budget"],
                "avatar"     => $project_avatar,
            ]
        );

        if (! $project) {
            return $this->sendError("Bad request", ["error" => "Project could not be created."]);
        }

        $project_team = $request->team
            ? Team::find($request->team)
            : Team::createProjectTeam($this->account(), $project, Account::getAccountInstance($this->account()));

        $project->teams()->syncWithoutDetaching([$project_team->id]);

        if ($request->teamMembers) {
            $team_members = json_decode($request->teamMembers, true);
            Team::addMembersToTeam($project_team, $team_members);
        }

        return $this->sendResponse(["project" => $project], "success, The project has been created successfully.");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/projects/{id}",
     *     tags={"Projects"},
     *     summary="Show a single project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Project fetched successfully"),
     *     @OA\Response(response=404, description="Project not found")
     * )
     */
    public function show($project_id)
    {
        $project = $this->account()->projects()
            ->with('tasks.dependencies', 'tasks.comments', 'tasks.assignees', 'tasks.media', 'comments.replies.user', 'teams.members')
            ->where('id', $project_id)
            ->first();

        if (! $project) {
            return $this->sendError("Bad request", ["error" => "The project could not be found."]);
        }

        return $this->sendResponse(["project" => $project], "success, The project has been fetched successfully.");
    }

    /**
     * @OA\Put(
     *     path="/api/v1/projects/{id}",
     *     tags={"Projects"},
     *     summary="Update an existing project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="overview", type="string"),
     *         @OA\Property(property="description", type="string"),
     *         @OA\Property(property="start_date", type="string"),
     *         @OA\Property(property="due_date", type="string"),
     *         @OA\Property(property="budget", type="number"),
     *         @OA\Property(property="status", type="string", enum={"pending", "ongoing", "finished", "overdue"}),
     *         @OA\Property(property="colors", type="string"),
     *         @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}),
     *         @OA\Property(property="avatar", type="string", format="binary")
     *     )),
     *     @OA\Response(response=200, description="Project updated successfully")
     * )
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            "name"        => ['required', 'string', Rule::unique('projects')->ignore($project->id)],
            "overview"    => ['required', 'string'],
            "description" => ['sometimes', 'string'],
            "start_date"  => ['required', 'string'],
            "due_date"    => ['required', 'string'],
            "budget"      => ['required'],
            "status"      => ['sometimes', 'string', 'in:' . implode(',', Project::PROJECT_STATUS)],
            "colors"      => ['sometimes', 'string'],
            "priority"    => ['sometimes', 'string', 'in:' . implode(',', Project::PROJECT_PRIORITIES)],
            "avatar"      => ['sometimes', 'file'],
        ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", $validator->errors());
        }

        $validated           = $validator->validated();
        $validated["avatar"] = $this->saveFile("projects", $request->avatar);

        $result = $this->account()->projects()->updateOrCreate(
            ['id' => $project->id],
            $validated
        );

        return $this->sendResponse(["project" => $result], "success, The project has been updated successfully.");
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/projects/{id}",
     *     tags={"Projects"},
     *     summary="Delete a project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Project deleted successfully"),
     *     @OA\Response(response=404, description="Project not found")
     * )
     */
    public function destroy($project_id)
    {
        $result = Project::destroy($project_id);

        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The project could not be deleted."]);
        }

        return $this->sendResponse(["deleted" => true], "success, The project has been deleted successfully.");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/projects/user/{user_id}",
     *     tags={"Projects"},
     *     summary="Get projects for a specific user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Projects fetched successfully"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function projectsForUser($user_id)
    {
        $projects = $this->account()->projects()
            ->whereHas('teams.members', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
            ->with('tasks.dependencies', 'tasks.comments', 'tasks.assignees', 'tasks.media', 'teams.members', 'comments')
            ->get();

        if (! $projects) {
            return $this->sendError("Bad request", ["error" => "The projects could not be fetched."]);
        }

        return $this->sendResponse(["projects" => $projects], "success, The projects have been fetched successfully.");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/projects/{project}/change-priority",
     *     tags={"Projects"},
     *     summary="Change the priority of a project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="priority", type="string", enum={"low", "medium", "high"})
     *     )),
     *     @OA\Response(response=200, description="Priority changed successfully")
     * )
     */
    public function changePriority(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'priority' => ['required', 'string', 'in:' . implode(',', Project::PROJECT_PRIORITIES)],
        ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", $validator->errors());
        }

        $project->update(['priority' => $request->priority]);

        return $this->sendResponse(["project" => $project], "Priority changed successfully.");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/projects/{project}/comments",
     *     tags={"Projects"},
     *     summary="Add a comment to a project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="content", type="string")
     *     )),
     *     @OA\Response(response=201, description="Comment added successfully")
     * )
     */
    public function addComment(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'content' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", $validator->errors());
        }

        $comment = $project->comments()->create([
            'content' => $request->content,
            'user_id' => Auth::id(),
        ]);

        return $this->sendResponse(["comment" => $comment], "Comment added successfully.");
    }

    /**
     * @OA\Put(
     *     path="/api/v1/projects/{project}/comments/{comment}",
     *     tags={"Projects"},
     *     summary="Update a comment on a project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="comment", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="content", type="string")
     *     )),
     *     @OA\Response(response=200, description="Comment updated successfully")
     * )
     */
    public function updateComment(Request $request, Project $project, Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            return $this->sendError("Unauthorized", ["error" => "You can only update your own comments."]);
        }

        $validator = Validator::make($request->all(), [
            'content' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", $validator->errors());
        }

        $comment->update(['content' => $request->content]);

        return $this->sendResponse(["comment" => $comment], "Comment updated successfully.");
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/projects/{project}/comments/{comment}",
     *     tags={"Projects"},
     *     summary="Delete a comment from a project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="comment", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Comment deleted successfully")
     * )
     */
    public function deleteComment(Project $project, Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            return $this->sendError("Unauthorized", ["error" => "You can only delete your own comments."]);
        }

        $comment->delete();

        return $this->sendResponse(["deleted" => true], "Comment deleted successfully.");
    }
}

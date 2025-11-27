<?php
namespace App\Http\Controllers;

use App\Helpers\Accounts\Account;
use App\Models\Campaign;
use App\Models\TaskDependancy;
use App\Models\Team;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Campaign",
 *     description="Manage marketing campaigns"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class CampaignController extends BaseController
{
    /**
     * Display a listing of the resource.
     */

    /**
     * @OA\Get(
     *     path="/api/v1/campaigns",
     *     operationId="getCampaigns",
     *     tags={"Campaign"},
     *     security={{"bearerAuth":{}}},
     *     summary="List all campaigns",
     *     description="Fetches all campaigns with their leads, user, tasks, teams and comments",
     *     @OA\Response(
     *         response=200,
     *         description="Campaigns fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="campaigns",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Campaign A"),
     *                         @OA\Property(property="description", type="string", example="Description of campaign"),
     *                         @OA\Property(property="leads", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="user", type="object"),
     *                         @OA\Property(property="tasks", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="teams", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="comments", type="array", @OA\Items(type="object"))
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully")
     *         )
     *     )
     * )
     */

    public function index()
    {
        //

        $campaigns = $this->account()->campaigns()
        ->with('leads')
            ->with("user")
            ->with("tasks")
            ->with("teams.members")
            ->with("comments")->get();

        return $this->sendResponse(["campaigns" => $campaigns], "Data fetched successfully");
    }

    /**
     * Show the form for creating a new resource.
     */

    /**
     * @OA\Get(
     *      path="/api/v1/campaigns/create",
     *      operationId="getCampaignCreateData",
     *      tags={"Campaign"},
     *      security={{"bearerAuth":{}}},
     *      summary="Get data needed to create a new campaign",
     *      @OA\Response(
     *          response=200,
     *          description="Data for campaign creation form fetched successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="campaign_defaults", type="object"),
     *              @OA\Property(property="campaign_status", type="array", @OA\Items(type="string")),
     *              @OA\Property(property="team_members", type="array", @OA\Items(type="object")),
     *              @OA\Property(property="task_dependencies", type="array", @OA\Items(type="string")),
     *              @OA\Property(property="targets", type="array", @OA\Items(type="string"))
     *          )
     *      )
     * )
     */
    public function create()
    {
        //

        $campaign_defaults = Campaign::CAMPAIGN_DEFAULTS;
        $campaign_status   = Campaign::CAMPAIGN_STATUS;
        $task_dependencies = TaskDependancy::TASK_DEPENDENCIES;
        $team_members      = $this->accountMembers();
        $targets           = Campaign::CAMPAIGN_TYPES;

        return $this->sendResponse([
            "campaign_defaults" => $campaign_defaults,
            "campaign_status"   => $campaign_status,
            "team_members"      => $team_members,
            "task_dependencies" => $task_dependencies,
            "targets"           => $targets,
        ], "Data fetched successfully");

    }

    /**
     * Store a newly created resource in storage.
     */

    /**
     * @OA\Post(
     *     path="/api/v1/campaigns",
     *     operationId="createCampaign",
     *     tags={"Campaign"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new campaign",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","description","budget","status","start_date","due_date"},
     *             @OA\Property(property="name", type="string", example="Summer Sales Campaign"),
     *             @OA\Property(property="description", type="string", example="Increase sales during summer season"),
     *             @OA\Property(property="avatar", type="string", format="binary"),
     *             @OA\Property(property="link", type="string", example="https://example.com/campaign"),
     *             @OA\Property(property="target", type="string", example="Retail Customers"),
     *             @OA\Property(property="budget", type="number", example=5000),
     *             @OA\Property(property="status", type="string", example="active"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-09-15"),
     *             @OA\Property(property="due_date", type="string", format="date", example="2025-10-15"),
     *             @OA\Property(property="teamMembers", type="string", example="[1,2,3]")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Campaign created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="campaign",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Summer Sales Campaign"),
     *                     @OA\Property(property="description", type="string", example="Increase sales during summer season"),
     *                     @OA\Property(property="avatar", type="string", example="avatar.png"),
     *                     @OA\Property(property="link", type="string", example="https://example.com/campaign"),
     *                     @OA\Property(property="target", type="string", example="Retail Customers"),
     *                     @OA\Property(property="budget", type="number", example=5000),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="start_date", type="string", format="date", example="2025-09-15"),
     *                     @OA\Property(property="due_date", type="string", format="date", example="2025-10-15"),
     *                     @OA\Property(property="teamMembers", type="string", example="[1,2,3]")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Success, campaign created successfully")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        //

        $validator = Validator::make($request->all(), [
            "name"        => ["required"],
            "description" => ["required"],
            "avatar"      => ['sometimes', 'file'],
            "link"        => ['sometimes', 'string'],
            "target"      => ["sometimes", "string"],
            "budget"      => ["required"],
            "status"      => ["required"],
            "start_date"  => ["required"],
            "due_date"    => ["required"],
        ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["errors" => $validator->errors()]);
        }

        $campaign_avatar = $this->saveFile("projects", $request->avatar);

        try {
            // Parse and validate the start_date
            $startDate               = new DateTime($request["start_date"], env('timezone '));
            $validated["start_date"] = $startDate->format('Y-m-d H:i:s');
            $dueDate                 = new DateTime($request["due_date"], env('timezone '));
            $validated["due_date"]   = $dueDate->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            // Handle the exception if the date is invalid
            info(["Invalid date provided: " => $e->getMessage()]);
        }

        $result = $this->account()->campaigns()->create([
            "name"        => $request->name,
            "description" => $request->description,
            "avatar"      => $campaign_avatar,
            "link"        => $request->link,
            "start_date"  => $validated["start_date"],
            "due_date"    => $validated["due_date"],
            "target"      => $request->target ? Campaign::CAMPAIGN_TARGET[$request->target] : null,
            "budget"      => $request->budget,
            "status"      => $request->status ? $request->status : Campaign::CAMPAIGN_STATUS[0],
        ]);

        if (! $result) {

            return $this->sendError("Failed to create campaign", ["errors" => $result]);

        }

        $campaign = $result;

        $campaign_team = null;
        if (! $request->team) {
            $campaign_team = Team::createProjectTeam($this->account(), $campaign, Account::getAccountInstance($this->account()));
        } else {
            $campaign_team = Team::where('id', $request->team)->first;
        }

        $added_campaign_team = $campaign->teams()->syncWithoutDetaching([$campaign_team->id]);

        if (! $added_campaign_team) {
            return $this->sendError("Bad request", ["error" => "The project team could not be created for the account.", "result" => $added_campaign_team]);
        }

        $add_team_members_result = null;
        if ($request->teamMembers && $added_campaign_team) {
            $team_members = [];

            $team_members = json_decode($request->teamMembers);

            $add_team_members_result = Team::addMembersToTeam($campaign_team, $team_members);

        }

        if (! $add_team_members_result) {
            return $this->sendError("Bad request", ["error" => "The project team members could not be added for the account.", "result" => $add_team_members_result]);
        }

        return $this->sendResponse(["campaign" => $result], "Success, campaign created successfully");

    }

    public function addMemberToCampaignTeam($campaign, Request $request)
    {

        $campaign = Campaign::where('id', $campaign)->first();

        if (! $campaign) {

            return $this->sendError("Failed to create campaign", ["errors" => $campaign]);

        }

        $campaign_team = null;
        if (! $request->team) {
            $campaign_team = Team::createProjectTeam($this->account(), $campaign, Account::getAccountInstance($this->account()));
        } else {
            $campaign_team = Team::where('id', $request->team)->first;
        }

        $added_campaign_team = $campaign->teams()->syncWithoutDetaching([$campaign_team->id]);

        if (! $added_campaign_team) {
            return $this->sendError("Bad request", ["error" => "The project team could not be created for the account.", "result" => $added_campaign_team]);
        }

        $add_team_members_result = null;
        if ($request->teamMembers && $added_campaign_team) {
            $team_members = [];

            $team_members = json_decode($request->teamMembers);

            $add_team_members_result = Team::addMembersToTeam($campaign_team, $team_members);

        }

        if (! $add_team_members_result) {
            return $this->sendError("Bad request", ["error" => "The project team members could not be added for the account.", "result" => $add_team_members_result]);
        }

        $campaign = Campaign::where('id', $campaign)->first();

        return $this->sendResponse(["campaign" => $campaign], "Success, campaign created successfully");

    }
    /**
     * Display the specified resource.
     */

    /**
     * @OA\Get(
     *     path="/api/v1/campaigns/{id}",
     *     operationId="getCampaignById",
     *     tags={"Campaign"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get campaign details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Campaign retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="campaign",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Summer Sales Campaign"),
     *                 @OA\Property(property="description", type="string", example="Increase sales during summer season"),
     *                 @OA\Property(property="avatar", type="string", example="avatar.png"),
     *                 @OA\Property(property="link", type="string", example="https://example.com/campaign"),
     *                 @OA\Property(property="target", type="string", example="Retail Customers"),
     *                 @OA\Property(property="budget", type="number", example=5000),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2025-09-15"),
     *                 @OA\Property(property="due_date", type="string", format="date", example="2025-10-15"),
     *                 @OA\Property(property="teamMembers", type="string", example="[1,2,3]")
     *             )
     *         )
     *     )
     * )
     */

    public function show($campaign)
    {
        //

        $campaign = Campaign::where('id', $campaign)->with(['leads',
            "user",
            "comments.user",
            "comments.replies.user",

        ])
            ->with('teams.members')
            ->with('tasks.dependencies')
            ->with('tasks.comments.user')
            ->with('tasks.comments.replies.user')
            ->with('tasks.assignees')
            ->with('tasks.media')
            ->first();

        if (! $campaign) {

            return $this->sendError("Failed to create campaign", ["errors" => $campaign]);

        }

        return $this->sendResponse(["campaign" => $campaign], "Success, campaign created successfully");

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($campaign)
    {
        //

        $campaign = Campaign::where('id', $campaign)->with(['leads',
            "user",
            "tasks",
            "comments"])->first();

        if (! $campaign) {

            return $this->sendError("Failed to create campaign", ["errors" => $campaign]);

        }

        $campaign_status = Campaign::CAMPAIGN_STATUS;

        return $this->sendResponse(["campaign" => $campaign, "campaign_status" => $campaign_status], "Data fetched successfully");

    }

    /**
     * Update the specified resource in storage.
     *
     *
     */

    /**
     * @OA\Put(
     *      path="/api/v1/campaigns/{id}",
     *      operationId="updateCampaign",
     *      tags={"Campaign"},
     *      security={{"bearerAuth":{}}},
     *      summary="Update a campaign",
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","description","image","link","target","budget","status"},
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="description", type="string"),
     *              @OA\Property(property="image", type="string"),
     *              @OA\Property(property="link", type="string"),
     *              @OA\Property(property="target", type="string"),
     *              @OA\Property(property="budget", type="number"),
     *              @OA\Property(property="status", type="string")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Campaign updated successfully"
     *      )
     * )
     */
    public function update(Request $request, $campaign)
    {
        //

        $campaign = Campaign::where('id', $campaign)->with(['leads',
            "user",
            "tasks",
            "comments"])->first();

        if (! $campaign) {

            return $this->sendError("Failed to find campaign", ["errors" => $campaign]);

        }

        $validator = Validator::make($request->all(), [
            "name"        => ["required"],
            "description" => ["required"],
            "image"       => ["required"],
            "link"        => ["required"],
            "target"      => ["required"],
            "budget"      => ["required"],
            "status"      => ["required"],
        ]);

        if ($validator->fails()) {
            $this->sendError("Bad request", ["errors" => $validator->errors()]);
        }

        $result = $campaign->update([
            "name"        => $request->name,
            "description" => $request->description,
            "image"       => $request->image,
            "link"        => $request->link,
            "target"      => $request->target,
            "budget"      => $request->budget,
            "status"      => $request->status,
        ]);

        if (! $result) {

            return $this->sendError("Failed to update campaign", ["errors" => $result]);

        }

        return $this->sendResponse(["campaign" => $result], "Success, campaign update successfully");

    }

    /**
     * @OA\Delete(
     *      path="/api/v1/campaigns/{id}",
     *      operationId="deleteCampaign",
     *      tags={"Campaign"},
     *      security={{"bearerAuth":{}}},
     *      summary="Delete a campaign",
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(
     *          response=200,
     *          description="Campaign deleted successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Success, campaign deleted successfully")
     *          )
     *      )
     * )
     */
    public function destroy($campaign)
    {
        //
        $result = Campaign::destroy($campaign);

        if (! $result) {
            return $this->sendError("Failed to create campaign", ["errors" => $result]);
        }

        return $this->sendResponse(["campaign" => $result], "Success, campaign created successfully");

    }
}

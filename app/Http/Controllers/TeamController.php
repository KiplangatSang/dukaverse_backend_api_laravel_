<?php
namespace App\Http\Controllers;

/**
 * @OA\Tag(
 *     name="Teams",
 *     description="API Endpoints for managing Teams"
 * )
 */
use OpenApi\Annotations as OA;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Http\Resources\ApiResource;
use App\Models\Team;
use App\Services\AuthService;
use App\Services\TeamService;
use App\Repositories\TeamsRepository;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Accounts\Account;
use App\Models\Retail;
use App\Models\Office;

class TeamController extends BaseController
{
    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource,
        private readonly TeamService $teamService
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/teams",
     *     tags={"Teams"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get list of teams",
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index()
    {
        try {
            $teams = $this->teamService->getIndexData();
            return $this->sendResponse($teams, "Teams fetched successfully");
        } catch (\Exception $e) {
            return $this->sendError('Error fetching teams', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/teams/create",
     *     tags={"Teams"},
     *     security={{"bearerAuth":{}}},
     *     summary="Show form for creating a team",
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function create()
    {
        try {
            $data = $this->teamService->getCreateData();
            return $this->sendResponse($data, "Create data fetched successfully");
        } catch (\Exception $e) {
            return $this->sendError('Error fetching create data', $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/teams",
     *     tags={"Teams"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new team",
     *     @OA\Response(response=201, description="Team created")
     * )
     */
    public function store(StoreTeamRequest $request)
    {
        try {
            $team = $this->teamService->createTeam($request);
            return $this->sendResponse($team, "Team created successfully");
        } catch (\Exception $e) {
            return $this->sendError('Error creating team', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/teams/{team}",
     *     tags={"Teams"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get a specific team",
     *     @OA\Parameter(name="team", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function show($team)
    {
        try {
            $teamData = $this->teamService->getShowData($team);
            return $this->sendResponse($teamData, "Team fetched successfully");
        } catch (\Exception $e) {
            return $this->sendError('Error fetching team', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/teams/{team}/edit",
     *     tags={"Teams"},
     *     security={{"bearerAuth":{}}},
     *     summary="Show form for editing a team",
     *     @OA\Parameter(name="team", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function edit($team)
    {
        try {
            $data = $this->teamService->getEditData($team);
            return $this->sendResponse($data, "Edit data fetched successfully");
        } catch (\Exception $e) {
            return $this->sendError('Error fetching edit data', $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/teams/{team}",
     *     tags={"Teams"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update a team",
     *     @OA\Parameter(name="team", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Team updated")
     * )
     */
    public function update(UpdateTeamRequest $request, $team)
    {
        try {
            $updatedTeam = $this->teamService->updateTeam($team, $request);
            return $this->sendResponse($updatedTeam, "Team updated successfully");
        } catch (\Exception $e) {
            return $this->sendError('Error updating team', $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/teams/{team}",
     *     tags={"Teams"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete a team",
     *     @OA\Parameter(name="team", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Team deleted")
     * )
     */
    public function destroy($team)
    {
        try {
            $result = $this->teamService->deleteTeam($team);
            return $this->sendResponse($result, "Team deleted successfully");
        } catch (\Exception $e) {
            return $this->sendError('Error deleting team', $e->getMessage());
        }
    }
}

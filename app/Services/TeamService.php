<?php

namespace App\Services;

use App\Repositories\TeamsRepository;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Accounts\Account;
use App\Models\Retail;
use App\Models\Office;
use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;

class TeamService extends BaseService
{
    protected $teamsRepository;

    public function __construct(
        TeamsRepository $teamsRepository,
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper,
        $account = null
    ) {
        parent::__construct($storeFileResource, $responseHelper);
        $this->teamsRepository = $teamsRepository;
        if ($account) {
            $this->teamsRepository->setAccount($account);
        }
    }

    public function getIndexData()
    {
        $teams = $this->teamsRepository->getAllTeams();
        return $teams;
    }

    public function getCreateData()
    {
        $team_types = $this->teamsRepository->getTeamTypes();
        $team_defaults = $this->teamsRepository->getTeamDefaults();

        return [
            "team_defaults" => $team_defaults,
            "team_types" => $team_types,
        ];
    }

    public function createTeam($request)
    {
        $validator = Validator::make($request->all(), [
            "name" => ["required"],
            "type" => ["required"],
            "color" => ["required"],
            "avatar" => ["required"],
        ]);

        if ($validator->fails()) {
            throw new \Exception("Validation failed: " . $validator->errors()->first());
        }

        $validated = $validator->validated();
        $validated['user_id'] = $this->user()->id;

        $team = $this->teamsRepository->createTeam($validated);

        if (!$team) {
            throw new \Exception("Failed to create team");
        }

        return $team;
    }

    public function getShowData($id)
    {
        $team = $this->teamsRepository->findTeam($id);

        if (!$team) {
            throw new \Exception("Team not found");
        }

        return $team;
    }

    public function getEditData($id)
    {
        $team = $this->teamsRepository->findTeam($id);

        if (!$team) {
            throw new \Exception("Team not found");
        }

        $team_types = $this->teamsRepository->getTeamTypes();
        $team_defaults = $this->teamsRepository->getTeamDefaults();

        return [
            "team" => $team,
            "team_defaults" => $team_defaults,
            "team_types" => $team_types,
        ];
    }

    public function updateTeam($id, $request)
    {
        $team = $this->teamsRepository->findTeam($id);

        if (!$team) {
            throw new \Exception("Team not found");
        }

        $validator = Validator::make($request->all(), [
            "name" => ["required"],
            "type" => ["required"],
            "color" => ["required"],
            "avatar" => ["required"],
        ]);

        if ($validator->fails()) {
            throw new \Exception("Validation failed: " . $validator->errors()->first());
        }

        $validated = $validator->validated();
        $validated['user_id'] = $this->user()->id;

        $result = $this->teamsRepository->updateTeam($team, $validated);

        if (!$result) {
            throw new \Exception("Failed to update team");
        }

        return $this->teamsRepository->findTeam($id);
    }

    public function deleteTeam($id)
    {
        $team = $this->teamsRepository->findTeam($id);

        if (!$team) {
            throw new \Exception("Team not found");
        }

        $result = $this->teamsRepository->deleteTeam($team);

        if (!$result) {
            throw new \Exception("Failed to delete team");
        }

        return $result;
    }
}

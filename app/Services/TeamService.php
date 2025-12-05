<?php

namespace App\Services;

use App\Services\TeamsService;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Accounts\Account;
use App\Models\Retail;
use App\Models\Office;
use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;
use App\Models\Team;

class TeamService extends BaseService
{

    public function __construct(
         StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper,
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }

    public function getIndexData()
    {
        $teams = $this->account->teams()->with('projects.tasks', 'tasks', 'employees.users')->get();;
        return $teams;
    }

    public function getCreateData()
    {
        $team_types = $this->getTeamTypes();
        $team_defaults =   Team::TEAM_DEFAULTS;

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

        $team = $this->account->teams()->create($validated);

        if (!$team) {
            throw new \Exception("Failed to create team");
        }

        return $team;
    }

    public function getShowData($id)
    {
        $team = $this->findTeam($id);

        if (!$team) {
            throw new \Exception("Team not found");
        }

        return $team;
    }

    public function getEditData($id)
    {
        $team = $this->findTeam($id);

        if (!$team) {
            throw new \Exception("Team not found");
        }

        $team_types = $this->getTeamTypes();
        $team_defaults =  Team::TEAM_DEFAULTS;

        return [
            "team" => $team,
            "team_defaults" => $team_defaults,
            "team_types" => $team_types,
        ];
    }

    public function updateTeam($id, $request)
    {
        $team = $this->findTeam($id);

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

        $result = $this->updateATeam($team, $validated);

        if (!$result) {
            throw new \Exception("Failed to update team");
        }

        return $this->findTeam($id);
    }

    public function deleteTeam($id)
    {
        $team = $this->findTeam($id);

        if (!$team) {
            throw new \Exception("Team not found");
        }

        $result = $team->delete();

        if (!$result) {
            throw new \Exception("Failed to delete team");
        }

        return $result;
    }
 
 
    public function findTeam($id)
    {
        return $this->account->teams()->where('id', $id)->first();
    }

    public function updateATeam($team, $data)
    {
        return $team->update($data);
    }

    public function getTeamTypes()
    {
        // Assuming account has a type or something, but for now, return defaults
        return Team::DUKAVERSETEAMTYPES; // or based on account
    }

    public function getTeamDefaults()
    {
        return Team::TEAM_DEFAULTS;
    }
}

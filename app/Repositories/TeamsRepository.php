<?php

namespace App\Repositories;

use App\Models\Team;

class TeamsRepository extends BaseRepository
{
    protected $model;

    public function __construct($account = null)
    {
        $this->model = new Team();
        $this->account = $account;
    }

    public function setAccount($account)
    {
        $this->account = $account;
    }

    public function getAllTeams()
    {
        return $this->account->teams()->with('projects.tasks', 'tasks', 'employees.users')->get();
    }

    public function createTeam($data)
    {
        return $this->account->teams()->create($data);
    }

    public function findTeam($id)
    {
        return $this->account->teams()->where('id', $id)->first();
    }

    public function updateTeam($team, $data)
    {
        return $team->update($data);
    }

    public function deleteTeam($team)
    {
        return $team->delete();
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

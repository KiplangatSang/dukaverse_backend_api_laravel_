<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    const RETALERTEAMTYPES = [
        'retail'    => 'retail',
        'dukaverse' => 'dukaverse',
        'ceroisoft' => 'ceroisoft',

    ];
    const DUKAVERSETEAMTYPES = [
        'dukaverse' => 'dukaverse',
    ];

    const TEAM_DEFAULTS = [
        "color"  => "#00FF00",
        'avatar' => "https://storage.googleapis.com/dukaverse-e4f47.appspot.com/app/nofile.png",
    ];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function teamable()
    {
        return $this->morphTo();
    }

    public function projects()
    {
        return $this->morphedByMany(Project::class, 'teamables', 'teamables');
    }

    public function tasks()
    {
        return $this->morphedByMany(Task::class, 'teamables');
    }

    public function employees()
    {
        return $this->morphedByMany(Employee::class, 'teamables');
    }

    public function members()
    {
        return $this->morphedByMany(User::class, 'teamables');
    }

    public static function createProjectTeam($account, $project, $team_type)
    {

        $team = $account->teams()->updateOrCreate([
            "name" => $project->name . " team"],
            [
                "type"    => $team_type,
                "user_id" => Auth::id(),
            ]);

        $result = $team->projects()->syncWithoutDetaching($project->id);
        if ($result) {
            return $team;
        } else {
            return false;
        }

    }

    public static function addMembersToTeam($team, $members)
    {

        $result = $team->members()->syncWithoutDetaching($members);
        return $result;

    }

}

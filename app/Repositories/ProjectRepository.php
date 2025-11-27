<?php
namespace App\Repositories;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProjectRepository
{
    private $account;
    public function __construct($account)
    {
        $this->account = $account;
    }

    public function getProjects($month = null, $year = null)
    {
        $projects = $this->account->projects()
            ->orderBy('created_at', 'DESC')
            ->get();

        return $projects;
    }

    public function getProjectsCount($month = null, $year = null)
    {
        $projects = $this->account->projects()
            ->orderBy('created_at', 'DESC')
            ->count();

        return $projects;
    }

    public function getTasks($month = null, $year = null)
    {
        $tasks = $this->account->tasks()
            ->orderBy('created_at', 'DESC')
            ->get();

        return $tasks;
    }

    public function getTasksCount($month = null, $year = null)
    {
        $tasks = $this->account->tasks()
            ->orderBy('created_at', 'DESC')
            ->count();

        return $tasks;
    }

    public function getProjectTeamMembers($month = null, $year = null)
    {
        $members  = 0;
        $projects = $this->account->projects()
            ->with('teams.members')
            ->get();

        if ($projects) {
            // Get the teams and then their members from each project
            $members = $projects->flatMap(function ($project) {
                return $project->teams->flatMap(function ($team) {
                    return $team->members;
                });
            })->unique('id');

            // $teams = $projects->pluck('teams')->pluck('members');
            // return $teams;

            // if ($teams) {
            //     $teams = collect($teams);

            //     $members = $projects->pluck('members');

            // }
        }

        return $members;
    }

    public function getTasksCompletionRate($month = null, $year = null)
    {

        $userId      = 1;                             // Replace with the relevant user ID
        $startPeriod = Carbon::now()->startOfMonth(); // Define the start of the period
        $endPeriod   = Carbon::now()->endOfMonth();   // Define the end of the period

        $totalTasks = $this->account->tasks()
            ->whereBetween('start_date', [$startPeriod, $endPeriod])
            ->count();

        $completedTasks = $this->account->tasks()
            ->whereBetween('start_date', [$startPeriod, $endPeriod])
            ->whereNotNull('date_closed')
            ->count();

        $completionRate = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

        return $completionRate;
    }

    public function averageTasksDuration($startPeriod = null, $endPeriod = null)
    {

        // $startPeriod = Carbon::parse('2025-01-27')->startOfDay(); // 2025-03-27 00:00:00
        // $endPeriod   = Carbon::parse('2025-03-27')->endOfDay();   // 2025-03-27 23:59:59
        $averageDuration = null;
        if ($startPeriod && $endPeriod) {
            $averageDuration = $this->account->tasks()
                ->whereNotNull('date_closed')
                ->whereBetween('start_date', [$startPeriod, $endPeriod])
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, start_date, date_closed)) as avg_duration')
                ->value('avg_duration');

        } else {
            $averageDuration = $this->account->tasks()
                ->whereNotNull('date_closed')
                ->whereBetween('start_date', [$startPeriod, $endPeriod])
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, start_date, date_closed)) as avg_duration')
                ->value('avg_duration');
        }

        return $averageDuration;
    }

    public function averageProgressOfOngoingTasks($startPeriod = null, $endPeriod = null)
    {
        $averageProgress = null;

        if ($startPeriod && $endPeriod) {
            $averageProgress = $this->account->tasks()
                ->whereNull('date_closed')
                ->whereBetween('start_date', [$startPeriod, $endPeriod])
                ->avg('progress');
        } else {
            $averageProgress = $this->account->tasks()
                ->whereNull('date_closed')
                ->avg('progress');
        }

        return $averageProgress;

    }

    public function getTasksStatus()
    {
        $taskCounts = $this->account->tasks()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        return $taskCounts;
    }

    public function getFinishedTasksCount()
    {
        $totalTasks     = $this->account->tasks()->count();
        $completedTasks = $this->account->tasks()->where('status', Task::FINISHED)->count();
        $overdue        = $this->account->tasks()->where('status', Task::OVERDUE)->count();
        $in_progress    = $this->account->tasks()->where('status', Task::ONGOING)->count();

        return ["totalTasks" => $totalTasks,
            "completedTasks"     => $completedTasks,
            "overdue"            => $overdue,
            "in_progress"        => $in_progress,
        ];
    }

    public function calculateProductivity()
    {
        $totalTasks     = $this->account->tasks()->count();
        $completedTasks = $this->account->tasks()->where('status', Task::FINISHED)->count();
        if ($totalTasks == 0 || $completedTasks == 0) {
            return 0;
        }

        $productivity = ($completedTasks / $totalTasks) * 100;

        return $productivity;
    }

    public function getTasksDueToday()
    {

        $today = Carbon::today();

        $tasksDueToday = $this->account->tasks()->with('assignees')->whereDate('end_date', $today)->get();

        return $tasksDueToday;
    }

    public function tasksDueTomorrow()
    {

        $tomorrow = Carbon::tomorrow();

        $tasksDueTomorrow = $this->account->tasks()->with('assignees')->whereDate('end_date', $tomorrow)->get();

        return $tasksDueTomorrow;
    }

    public function tasksDueThisWeek()
    {

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek   = Carbon::now()->endOfWeek();

        $tasksDueThisWeek = $this->account->tasks()->with('assignees')->whereBetween('end_date', [$startOfWeek, $endOfWeek])->get();

        return $tasksDueThisWeek;

    }
    public function tasksDueThisMonth()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now()->endOfMonth();

        $tasksDueThisMonth = $this->account->tasks()->with('assignees')->whereBetween('end_date', [$startOfMonth, $endOfMonth])->get();

        return $tasksDueThisMonth;
    }

    public function recentActivities()
    {

        $notifications = $this->account->notifications()
            ->where('type', 'projects')
            ->orWhere('type', 'tasks')
            ->get();

        return $notifications;
    }

}

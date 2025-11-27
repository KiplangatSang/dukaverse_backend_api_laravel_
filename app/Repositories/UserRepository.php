<?php
namespace App\Repositories;

use App\Helpers\Accounts\Account;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    private $account;
    private $user;
    public function __construct($user = null)
    {

        if (! $user) {
            $this->user = User::where('id', Auth::id())->first();
        } else {
            $this->user = $user;
        }

    }
    public function getDisctictUsers()
    {

        $account = new Account($this->user);
        $this->account = $account;
        $users   = $account->members();

        return $users;
    }

    private function getUserIds($users)
    {
        $user_ids = $users->pluck('id')->toArray();
        return $user_ids;
    }
    public function recentUsers($days = 30)
    {
        $users    = $this->getDisctictUsers();
        $user_ids = $this->getUserIds($users);

        $dateThreshold = Carbon::now()->subDays($days);

        $recent_users = User::whereIn('id', $user_ids)
            ->where('created_at', '>=', $dateThreshold)
            ->get();

        return $recent_users;
    }

    public function activeUsers($days = 10)
    {
        $users    = $this->getDisctictUsers();
        $user_ids = $this->getUserIds($users);

        $dateThreshold = Carbon::now()->subDays($days);

        $recent_users = User::whereIn('id', $user_ids)
        // ->where('last_login', '<=', $dateThreshold)
            ->count();

        return $recent_users;
    }

    public function getUsersTraffic()
    {
        $users = $this->getDisctictUsers();

        $user_ids = $users->pluck('id')->toArray();

        $loginStatistics = User::whereIn('id', $user_ids)
            ->select('login_type', DB::raw('count(*) as total'))
            ->groupBy('login_type')
            ->get();

        return $loginStatistics;
    }

    public function getUsersTrafficByCountry()
    {
        $users = $this->getDisctictUsers();

        $user_ids = $users->pluck('id')->toArray();

        $loginStatistics = DB::table('profiles')
            ->whereIn('user_id', $user_ids)
            ->select('country', DB::raw('count(*) as total'))
            ->groupBy('country')
            ->get();

        return $loginStatistics;
    }

    public function getChannelVisits()
    {
        $users = $this->getDisctictUsers();

        $user_ids = $this->getUserIds($users);

        $direct = User::whereIn('id', $user_ids)
            ->whereNull('referal_id')
            ->where('login_type', 'direct')
        // ->select('login_type', DB::raw('count(*) as total'))
        // ->groupBy('login_type')
            ->count();

        $search_find_out = User::whereIn('id', $user_ids)
            ->whereNull('referal_id')
            ->where('find_out_site', 'search')
        // ->select('login_type', DB::raw('count(*) as total'))
        // ->groupBy('login_type')
            ->count();

        $referals = User::whereIn('id', $user_ids)
            ->whereNotNull('referal_id')
        // ->select('referal_id', DB::raw('count(*) as total'))
        // ->groupBy('login_type')
            ->count();

        $socials_find_out = User::whereIn('id', $user_ids)
            ->whereNull('referal_id')
            ->where('find_out_site', 'socials')
// ->select('login_type', DB::raw('count(*) as total'))
        // ->groupBy('login_type')
            ->count();
        $total = count($this->getDisctictUsers());

        return [
            "direct"           => [
                "name"       => "direct",
                "count"      => $direct,
                "percentage" => $direct > 0 ? (($direct / $total) * 100) : 0,
            ],
            "search_find_out"  => [
                "name"       => "search_find_out",
                "count"      => $search_find_out,
                "percentage" => $search_find_out > 0 ? (($search_find_out / $total) * 100) : 0,
            ],
            "referals"         => [
                "name"       => "referals",
                "count"      => $referals,
                "percentage" => $referals > 0 ? (($referals / $total) * 100) : 0,
            ],
            "socials_find_out" => [
                "name"       => "socials_find_out",
                "count"      => $socials_find_out,
                "percentage" => $socials_find_out > 0 ? (($socials_find_out / $total) * 100) : 0,
            ],
        ];
    }

    //getUserById
    public function getUserById($id)
    {
        $users = $this->getDisctictUsers();

        $user = $users
            ->where('id', $id)
            ->first();

        return $user;
    }

    //get users ByDate
    public function getUsersByDate($startDate, $endDate)
    {
        $users = $this->getDisctictUsers();

        $filtered_users = $users->whereBetween('created_at', [$startDate . " 00:00:00", $endDate . " 23:59:59"])->get();
        return $filtered_users;
    }

    public function getUsers($month, $year = null)
    {

        $users = $this->getDisctictUsers();

        $user_ids = $this->getUserIds($users);

        if (! $year) {
            $year = date("Y");
        }
        $filtered_users = null;
        if ($month) {
            $filtered_users = User::whereIn('id', $user_ids)
                ->whereMonth("created_at", $month)
                ->whereYear('created_at', '=', $year)
                ->get();
        } else {
            $filtered_users = User::whereIn('id', $user_ids)
                ->whereYear('created_at', '=', $year)
                ->get();
        }

        //dd($sales);
        return $filtered_users;
    }

    public function getUsersByKeys($key, $value)
    {

        $users    = $this->getDisctictUsers();
        $user_ids = $this->getUserIds($users);

        $filtered_users = null;
        $filtered_users = User::whereIn('id', $user_ids)->where($key, $value)->orderBy('created_at', 'DESC')->get();
        return $filtered_users;
    }

    public function getUsersGrowth($month = null, $year = null)
    {

        if (! $month) {
            $month = date('m');
        }

        if (! $year) {
            $year = date("Y");
        }

        $currentUsers  = count($this->getUsers($month));
        $previousUsers = count($this->getUsers($month - 1));

        //dd("$currentRevenue");

        if ($currentUsers <= 0) {
            $previousUsers = 1;
        }

        $growth = (($currentUsers - $previousUsers) / $currentUsers) * 100;
        $growth = number_format($growth, 2);

        return $growth;
    }

    public function getViewsPerMinute($month = null, $year = null)
    {
        if (! $month) {
            $month = date('m');
        }

        if (! $year) {
            $year = date("Y");
        }

        $startTime = Carbon::now()->subMinutes(30); // Adjust the time frame as needed

        $viewsPerMinute = $this->account->activities()
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") as time'), DB::raw('COUNT(*) as views'))
            ->where('activity_type', 'page_view')
            ->where('created_at', '>=', $startTime)
            ->whereMonth("created_at", $month)
            ->whereYear('created_at', '=', $year)
            ->groupBy('time')
            ->orderBy('time', 'asc')
            ->get();

        return $viewsPerMinute;

    }

    public function getViewsPerMonth($month = null, $year = null)
    {
        if (! $month) {
            $month = date('m');
        }

        if (! $year) {
            $year = date("Y");
        }

        $startTime = Carbon::now()->subMinutes(30); // Adjust the time frame as needed

        $viewsPerMinute = $this->account->activities()
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as time'), DB::raw('COUNT(*) as views'))
            ->where('activity_type', 'page_view')
            ->where('created_at', '>=', $startTime)
            ->whereYear('created_at', '=', $year)
            ->groupBy('time')
            ->orderBy('time', 'asc')
            ->get();

        return $viewsPerMinute;

    }

    public function getPageViewsPerMinute($page, $month = null, $year = null)
    {
        $startTime = Carbon::now()->subMinutes(30); // Adjust the time frame as needed

        if (! $month) {
            $month = date('m');
        }

        if (! $year) {
            $year = date("Y");
        }

        $viewsPerMinute = $this->account->activities()
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") as minute'), DB::raw('COUNT(*) as views'))
            ->where('activity_type', 'page_view')
            ->where('page', $page)
            ->where('created_at', '>=', $startTime)
            ->whereMonth("created_at", $month)
            ->whereYear('created_at', '=', $year)
            ->groupBy('minute')
            ->orderBy('minute', 'asc')
            ->get();

        return $viewsPerMinute;

    }

    public function getViewsGrowth($month = null, $year = null)
    {

        if (! $month) {
            $month = date('m');
        }

        if (! $year) {
            $year = date("Y");
        }

        $currentViews  = count($this->getViewsPerMinute($month));
        $previousViews = count($this->getViewsPerMinute($month - 1));

//dd("$currentRevenue");

        if ($currentViews <= 0) {
            $previousViews = 1;
        }

        $growth = (($currentViews - $previousViews) / $currentViews) * 100;
        $growth = number_format($growth, 2);

        return $growth;

    }

    public function calculateBounceRate($page)
    {
        $totalSessions = $this->account->activities()
            ->where('activity_type', 'page_view')
            ->where('page', $page)
            ->count();

        $singlePageSessions = $this->account->activities()
            ->where('activity_type', 'page_view')
            ->where('page', $page)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('user_activities as a2')
                    ->whereRaw('a2.user_id = user_activities.user_id')
                    ->whereRaw('a2.created_at > user_activities.created_at');
            })
            ->count();

        $bounceRate = ($totalSessions > 0) ? ($singlePageSessions / $totalSessions) * 100 : 0;

        return $bounceRate;
    }

    public function getTopPages($pageLimit = 10)
    {
        $topPages = $this->account->activities()
            ->select('page', DB::raw('count(*) as views'))
            ->groupBy('page')
            ->orderByDesc('views')
            ->limit($pageLimit)
            ->get();

        return $topPages;

    }

    public function getChannelVisitsByOS()
    {
        $topOSVersions = $this->account->activities()
            ->select('operating_system', DB::raw('count(*) as views'))
            ->groupBy('operating_system')
            ->orderByDesc('views')
            ->get();

        return $topOSVersions;

    }

    public function getTopPagesWithBounceRate($pageLimit = 10)
    {
        $topPages = $this->account->activities()
            ->select('page', DB::raw('count(*) as views'))
            ->groupBy('page')
            ->orderByDesc('views')
            ->limit($pageLimit)
            ->get();

        foreach ($topPages as $page) {
            $page->bounce_rate = $this->calculateBounceRate($page->page);
        }

        return $topPages;

    }

    public function getTopPagesByBounceRate($pageLimit = 10)
    {
        $pages = $this->account->activities()
            ->select('page', DB::raw('COUNT(DISTINCT session_id) as total_sessions'))
            ->groupBy('page')
            ->get();

        $pagesWithBounceRate = $pages->map(function ($page) {
            $singlePageSessions = $this->account->activities()
                ->where('page', $page->page)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('user_activities as a2')
                        ->whereRaw('a2.session_id = user_activities.session_id')
                        ->whereRaw('a2.id != user_activities.id');
                })
                ->distinct('session_id')
                ->count('session_id');

            $page->bounce_rate = $page->total_sessions > 0 ? ($singlePageSessions / $page->total_sessions) * 100 : 0;
            return $page;
        });

        return $pagesWithBounceRate->sortByDesc('bounce_rate')->take($pageLimit);
    }

    public function getEngagementOverview()
    {
        $sessions = $this->account->activities()
            ->select('session_id', DB::raw('MIN(created_at) as start_time'), DB::raw('MAX(created_at) as end_time'))
            ->groupBy('session_id')
            ->get();

        $durationRanges = [
            '0-30'    => ['min' => 0, 'max' => 30, 'sessions' => 0, 'views' => 0],
            '31-60'   => ['min' => 31, 'max' => 60, 'sessions' => 0, 'views' => 0],
            '61-120'  => ['min' => 61, 'max' => 120, 'sessions' => 0, 'views' => 0],
            '121-240' => ['min' => 121, 'max' => 240, 'sessions' => 0, 'views' => 0],
        ];

        foreach ($sessions as $session) {
            $duration = Carbon::parse($session->start_time)->diffInSeconds(Carbon::parse($session->end_time));

            foreach ($durationRanges as $range => &$data) {
                if ($duration >= $data['min'] && $duration <= $data['max']) {
                    $data['sessions']++;
                    $data['views'] += $this->account->activities()->where('session_id', $session->session_id)->count();
                    break;
                }
            }
        }

        return $durationRanges;
    }

}

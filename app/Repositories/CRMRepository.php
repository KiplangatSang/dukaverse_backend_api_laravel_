<?php
namespace App\Repositories;

use App\Helpers\Accounts\Account;
use App\Models\Lead;
use App\Models\SaleTransaction;
use App\Models\User;

class CRMRepository
{
    private $account;
    private $user;
    public function __construct($account, $user = null)
    {
        $this->account = $account;

        if (! $user) {
            $this->user = auth()->user();
        } else {
            $this->user = $user;
        }

    }

    public function getCampaigns()
    {

        $campaigns = $this->account->campaigns()->get();
        return $campaigns;
    }

    public function getCampaignsCount()
    {

        $campaigns = $this->account->campaigns()->count();
        return $campaigns;
    }

    public function getMonthlyCampaigns($month = null, $year = null)
    {

        if (! $year) {
            $year = date("Y");
        }

        if (! $month) {
            $month = date('m');
        }

        $monthly_campaigns = $this->account->campaigns()
            ->whereMonth("created_at", $month)
            ->whereYear('created_at', '=', $year)
            ->get();

        return $monthly_campaigns;

    }

    public function getCampaignsGrowth($month = null, $year = null)
    {

        if (! $month) {
            $month = date('m');
        }

        $currentCampaigns  = count($this->getMonthlyCampaigns($month));
        $previousCampaigns = count($this->getMonthlyCampaigns($month - 1));

        //dd("$currentRevenue");

        if ($currentCampaigns <= 0) {
            $previousCampaigns = 1;
            $currentCampaigns  = 1;
        }

        $growth = (($currentCampaigns - $previousCampaigns) / $currentCampaigns) * 100;
        $growth = number_format($growth, 2);

        return $growth;
    }

    public function getNewLeads($limit = 100)
    {

        $leads = $this->account->leads()->orderBy(
            'created_at', 'desc'
        )
            ->take($limit)
            ->get();
        return $leads;

    }

    public function getNewLeadsCount($limit = 100)
    {

        $leads = $this->account->leads()->orderBy(
            'created_at', 'desc'
        )
            ->take($limit)
            ->count();
        return $leads;

    }

    public function getMonthlyLeads($month = null, $year = null)
    {

        if (! $year) {
            $year = date("Y");
        }

        if (! $month) {
            $month = date('m');
        }

        $monthly_leads = $this->account->leads()
            ->whereMonth("created_at", $month)
            ->whereYear('created_at', '=', $year)
            ->get();

        return $monthly_leads;

    }

    public function getLeadsGrowth($month = null, $year = null)
    {

        if (! $month) {
            $month = date('m');
        }

        $currentLeads  = count($this->getMonthlyLeads($month));
        $previousLeads = count($this->getMonthlyLeads($month - 1));

        //dd("$currentRevenue");

        if ($currentLeads <= 0) {
            $previousLeads = 1;
            $currentLeads  = 1;

        }

        $growth = (($currentLeads - $previousLeads) / $currentLeads) * 100;
        $growth = number_format($growth, 2);

        return $growth;
    }

    public function getDeals()
    {

        $lead_deals = 0;
        $leads      = $this->account->leads()
            ->whereHas('transactions')
            ->with('transactions')
            ->get();

        if ($leads) {

            $transactions = $leads->pluck('transactions');
            $lead_deals   = $transactions->sum('total_amount');
        }

        return $lead_deals;

    }

    public function getMonthlyDeals($month = null, $year = null)
    {

        if (! $year) {
            $year = date("Y");
        }

        if (! $month) {
            $month = date('m');
        }

        $lead_deals = 0;

        $leads = $this->account->leads()
            ->whereHas('transactions')
            ->with('transactions', function ($query) use ($month, $year) {
                $query->whereMonth('created_at', $month)
                    ->whereYear('created_at', '=', $year);
            })
            ->get();

        if ($leads) {

            $transactions = $leads->pluck('transactions');
            $lead_deals   = $transactions->sum('total_amount');
        }

        return $lead_deals;
    }

    public function getDealsGrowth($month = null, $year = null)
    {

        if (! $month) {
            $month = date('m');
        }

        $currentDeals  = $this->getMonthlyDeals($month);
        $previousDeals = $this->getMonthlyDeals($month - 1);

        //dd("$currentRevenue");

        if ($currentDeals <= 0) {
            $previousDeals = 1;
            $currentDeals  = 1;

        }

        $growth = (($currentDeals - $previousDeals) / $currentDeals) * 100;
        $growth = number_format($growth, 2);

        return $growth;
    }

    public function getBookedRevenue()
    {

        $booked_revenue = 0;

        $leads = $this->account->leads()
            ->whereHas('transactions')
            ->with('transactions')
            ->get();

        if ($leads) {

            $transactions = $leads->pluck('transactions');

            $booked_revenue = $transactions->where('payment_stage', SaleTransaction::PAID)
                ->sum('paid_amount');

        }

        return $booked_revenue;

    }

    public function getMonthlyBookedRevenue($month = null, $year = null)
    {

        $booked_revenue = 0;

        $leads = $this->account->leads()
            ->whereHas('transactions')
            ->with('transactions', function ($query) use ($month, $year) {
                $query->whereMonth('created_at', $month)
                    ->whereYear('created_at', '=', $year);
            })
            ->get();

        if ($leads) {

            $transactions = $leads->pluck('transactions');

            $booked_revenue = $transactions->where('payment_stage', SaleTransaction::PAID)
                ->sum('paid_amount');

        }

        return $booked_revenue;

    }

    public function getBookedRevenueGrowth($month = null, $year = null)
    {

        if (! $month) {
            $month = date('m');
        }

        $currentRevenue  = ($this->getMonthlyBookedRevenue($month));
        $previousRevenue = ($this->getMonthlyBookedRevenue($month - 1));

        //dd("$currentRevenue");

        if ($currentRevenue <= 0) {
            $currentRevenue = 1;

            $previousRevenue = 1;
        }

        $growth = (($currentRevenue - $previousRevenue) / $currentRevenue) * 100;
        $growth = number_format($growth, 2);

        return $growth;
    }

    public function getMonthlyBookedRevenueGrowth($month = null)
    {
        if (! $month) {
            $month = date('m');
        }

        $currentRevenue  = ($this->getMonthlyBookedRevenue($month));
        $previousRevenue = ($this->getMonthlyBookedRevenue($month - 1));

        //dd("$currentRevenue");

        if ($currentRevenue <= 0) {
            $previousRevenue = 1;
            $currentRevenue  = 1;

        }

        $growth = (($currentRevenue - $previousRevenue) / $currentRevenue) * 100;
        $growth = number_format($growth, 2);

        return $growth;

    }

    public function getCampaignsState()
    {

        //   Total Sent,
        //    Reached,
        //     Opened

        $total_leads   = $this->account->leads()->count();
        $reached_leads = $this->account->leads()
            ->where('status', '!=', Lead::PENDING_LEAD)->count();
        $opened_leads = $this->account->leads()
            ->where('status', Lead::WON_LEAD)
            ->orWhere('status', Lead::LOST_LEAD)
            ->count();
        $cold_leads = $this->account->leads()
            ->where('status', Lead::COLD_LEAD)
            ->count();

        $pending_leads = $this->account->leads()
            ->where('status', Lead::PENDING_LEAD)
            ->count();

        return [
            "total_leads"   => $total_leads,
            "reached_leads" => $reached_leads,
            "opened_leads"  => $opened_leads,
            "cold_leads"    => $cold_leads,
            "pending_leads" => $pending_leads,
        ];
    }

    public function getTopCampaigners()
    {

        // User	Leads	Deals	Tasks

        $account = new Account($this->user);
        $users   = $account->members();

        $user_ids       = $this->getUserIds($users);
        $topCampaigners = User::whereIn('id', $user_ids)
            ->whereHas('userLeads')              // Ensure the user has leads
            ->withCount('assignedCampaignTasks') // Load the tasks
            ->withCount('userLeads')             // Count the number of leads for each user 
            ->orderByDesc('user_leads_count')    // Order by the count of leads in descending order
            ->get();

        return $topCampaigners;

    }

    private function getUserIds($users)
    {
        $user_ids = $users->pluck('id')->toArray();
        return $user_ids;
    }

    public function getRecentLeads()
    {

        $recent_leads = $this->account->leads()->orderBy('created_at', 'desc')->take(100)->get();
        return $recent_leads;
    }

    public function getTodos()
    {
        $account_todos = $this->account->todos()->get();

        $user_todos = $this->user->todos()->get();

        $todos = $account_todos->merge($user_todos);

        return $todos;
    }
}

<?php
namespace App\Services;

use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\User;
use App\Repositories\UserRepository;

class AnalyticsService extends BaseService
{

    public function __construct(
        private readonly UserRepository $user_repository,
        private readonly StoreFileResource $storeFileResource,
        private readonly ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }

    public function index()
    {

        $data = $this->appRepository->getAppData();

        return $this->responseHelper->respond(['data' => $data], "Data fetched successfully", 200);
    }

    public function permissions()
    {
        $permissions = [];

        $account_permissions = $this->account->authenticatedUserPermissions();
        $permissions         = $account_permissions;

        return $this->responseHelper->respond(['permissions' => (array) $permissions], "Permissions fetched successfully", 200);
    }

    public function showUser()
    {

        $user     = $this->user();
        $user     = User::where('id', $user->id)->firstOrFail();
        $profile  = $user->profile;
        $messages = $user->messages;

        return $this->responseHelper->respond(["user" => $user,
            "profile"                                     => $profile,
        ], "User data fetched successfully", 200);
    }

    public function notifications()
    {

        $user          = $this->user();
        $user          = User::where('id', $user->id)->firstOrFail();
        $notifications = $user->unreadNotifications()->latest()->get();

        return $this->responseHelper->respond([
            "notifications" => $notifications,
        ], "User data fetched successfully", 200);
    }

    public function messages()
    {
        $user     = $this->user();
        $user     = User::where('id', $user->id)->firstOrFail();
        $messages = $user->messages;

        return $this->responseHelper->respond([
            "messages" => $messages,
        ], "User data fetched successfully", 200);
    }

    public function dashboardAnalytics()
    {

        // $this->user_repository = new UserRepository($this->account(), $this->user());
        $activeUsers    = $this->user_repository->activeUsers();
        $user_growth    = $this->user_repository->getUsersGrowth();
        $viewsPerMinute = $this->user_repository->getViewsPerMinute();
        $viewsPerMonth  = $this->user_repository->getViewsPerMonth();

        $viewsGrowth                = $this->user_repository->getViewsGrowth();
        $top_pages                  = $this->user_repository->getTopPages();
        $top_pages_with_bounce_rate = $this->user_repository->getTopPages();
        $page_bounce_rate           = $this->user_repository->getTopPagesByBounceRate();
        $engagementOverview         = $this->user_repository->getEngagementOverview();
        $media_traffic              = $this->user_repository->getUsersTraffic();
        $channel_visits             = $this->user_repository->getChannelVisits();
        $country_sessions           = $this->user_repository->getUsersTrafficByCountry();

        $os_sessions = $this->user_repository->getChannelVisitsByOS();

        return $this->responseHelper->respond([
            "activeUsers"                => $activeUsers,
            "user_growth"                => $user_growth,
            "viewsPerMinute"             => $viewsPerMinute,
            "viewsPerMonth"              => $viewsPerMonth,
            "viewsGrowth"                => $viewsGrowth,
            "top_pages"                  => $top_pages,
            "top_pages_with_bounce_rate" => $top_pages_with_bounce_rate,
            "page_bounce_rate"           => $page_bounce_rate,
            "engagementOverview"         => $engagementOverview,
            "media_traffic"              => $media_traffic,
            "channel_visits"             => $channel_visits,
            "os_sessions"                => $os_sessions,
            "country_sessions"           => $country_sessions,
        ], "Data fetched successfully", 200);

    }

    public function dashboardProjects()
    {

        $total_projects = $this->project_repository->getProjectsCount();

        $total_tasks = $this->project_repository->getTasksCount();

        $project_team_members = $this->project_repository->getProjectTeamMembers();
        $tasks_status         = $this->project_repository->getTasksStatus();
        $finishedTasks        = $this->project_repository->getFinishedTasksCount();
        $tasksDueToday        = $this->project_repository->getTasksDueToday();
        $tasksDueTomorrow     = $this->project_repository->tasksDueTomorrow();
        $tasksDueThisWeek     = $this->project_repository->tasksDueThisWeek();
        $tasksDueThisMonth    = $this->project_repository->tasksDueThisMonth();
        $productivity         = $this->project_repository->calculateProductivity();
        $recent_activity      = $this->project_repository->recentActivities();

        return $this->responseHelper->respond(
            [
                "productivity"         => $productivity,
                "total_projects"       => $total_projects,
                "total_tasks"          => $total_tasks,
                "project_team_members" => $project_team_members,
                "tasks_status"         => $tasks_status,
                "finishedTasks"        => $finishedTasks,
                "tasksDueToday"        => $tasksDueToday,
                "tasksDueTomorrow"     => $tasksDueTomorrow,
                "tasksDueThisWeek"     => $tasksDueThisWeek,
                "tasksDueThisMonth"    => $tasksDueThisMonth,
                "recent_activity"      => $recent_activity,
            ], "Data fetched successfully.", 200
        );
    }

    public function dashboarEcommerce()
    {

        $customers        = $this->ecommerce_repository->getCustomersCount();
        $customers_growth = $this->ecommerce_repository->getCustomersGrowth();

        $orders = $this->ecommerce_repository->getOrders();

        $orders_growth = $this->ecommerce_repository->getOrdersGrowth();

        $sales_growth         = $this->ecommerce_repository->salesGrowth();
        $monthly_sales_growth = $this->ecommerce_repository->monthlySalesGrowth();

        $revenue_growth = $this->ecommerce_repository->getRevenueGrowth();

        $monthly_revenue        = $this->ecommerce_repository->getRevenuePerMonth();
        $previous_month_revenue = $this->ecommerce_repository->getPreviousMonthRevenue();

        $locations_revenue  = $this->ecommerce_repository->getRevenuePerLocation();
        $total_days_revenue = $this->ecommerce_repository->getTotalDaysRevenue();

        // Top Selling Products

        $top_products = $this->ecommerce_repository->getTopSellingProducts();
        // Total Sales

        $saleTypes = $this->ecommerce_repository->getSaleTypes();

        // Recent Activity

        $recent_activity = $this->ecommerce_repository->getRecentActivity();

        //graph data
        $graph_data = $this->ecommerce_repository->getGraphData();

        return $this->responseHelper->respond([
            "customers"              => $customers,
            "customers_growth"       => $customers_growth,
            "orders"                 => $orders,
            "orders_growth"          => $orders_growth,
            "orders"                 => $orders,
            "revenue_growth"         => $revenue_growth,
            "monthly_revenue"        => $monthly_revenue,
            "previous_month_revenue" => $previous_month_revenue,
            "sales_growth"           => $sales_growth,
            "monthly_sales_growth"   => $monthly_sales_growth,
            "locations_revenue"      => $locations_revenue,
            "top_products"           => $top_products,
            "total_days_revenue"     => $total_days_revenue,
            "sale_types"             => $saleTypes,
            "recent_activity"        => $recent_activity,
            "graph_data"             => $graph_data,
        ], "Sucess, data fetched successfully.", 200);
    }

    public function dashboardCRM()
    {

        //   Campaign Sent
        $campaigns_sent   = $this->crm_repository->getCampaignsCount();
        $campaigns_growth = $this->crm_repository->getCampaignsGrowth();

        // New Leads
        $newLeads       = $this->crm_repository->getNewLeadsCount();
        $newLeadsGrowth = $this->crm_repository->getLeadsGrowth();

        //deals
        $campaigns_deals = $this->crm_repository->getDeals();

        $campaigns_deals_growth = $this->crm_repository->getDealsGrowth();

        //booked revenue
        $campaigns_booked_revenue        = $this->crm_repository->getBookedRevenue();
        $campaigns_booked_revenue_growth = $this->crm_repository->getBookedRevenueGrowth();

        // Campaigns stats
        //   Totals,  Total Sent, Reached, Opened
        $campaign_statistics = $this->crm_repository->getCampaignsState();

        //monthly revenue growth
        $campaigns_month_revenue          = $this->crm_repository->getMonthlyBookedRevenue();
        $campaigns_previous_month_revenue = $this->crm_repository->getMonthlyBookedRevenue($month = date('m') - 1);

        // Top Performing
        $top_campaigners = $this->crm_repository->getTopCampaigners();

        // Recent Leads
        $recent_leads = $this->crm_repository->getRecentLeads();

        // Todos
        $todos = $this->crm_repository->getTodos();

        return $this->responseHelper->respond([
            "campaigns_sent"                   => $campaigns_sent,
            "campaigns_growth"                 => $campaigns_growth,
            "newLeads"                         => $newLeads,
            "newLeadsGrowth"                   => $newLeadsGrowth,
            "campaigns_deals"                  => $campaigns_deals,
            "campaigns_deals_growth"           => $campaigns_deals_growth,
            "campaigns_booked_revenue"         => $campaigns_booked_revenue,
            "campaigns_booked_revenue_growth"  => $campaigns_booked_revenue_growth,
            "campaign_statistics"              => $campaign_statistics,
            "campaigns_month_revenue"          => $campaigns_month_revenue,
            "campaigns_previous_month_revenue" => $campaigns_previous_month_revenue,
            "top_campaigners"                  => $top_campaigners,
            "recent_leads"                     => $recent_leads,
            "todos"                            => $todos,
        ], "Success, Data fetched successfully.", 200);

    }

    public function dashboadWallet()
    {

        // Accounts Balance

        $wallets = $this->wallet_repository->getWallets();
        // Sales

        $sales        = $this->wallet_repository->getSales();
        $sales_growth = $this->wallet_repository->getSalesGrowth();

        // Expenses
        $expenses     = $this->wallet_repository->getExpenses();
        $sales_growth = $this->wallet_repository->getExpensesGrowth();
        // Revenue

        $expenses     = $this->wallet_repository->getRevenue();
        $sales_growth = $this->wallet_repository->getRevenueGrowth();

        // Currencies Used
        // Ksh

        $expenses = $this->wallet_repository->getCurrencies();

        //  BalanceStatus
        $balanceStatus = $this->wallet_repository->getBalanceStatus();

        //  Graph database_path('')
        //  Based on transactions

        //  Account Suggestions
        //  Payment Methods
        $payment_gateways = $this->wallet_repository->getTransactionGateways();

        //  Money History
        //  Income
        //  Expenses
        // Transfar

        $money_history = $this->wallet_repository->getMoneyHistory();

        //   Transaction history

        $transaction_history = $this->wallet_repository->getTransactionHistory();

        return $this->responseHelper->respond([
            "wallets"             => $wallets,
            "sales"               => $sales,
            "sales_growth"        => $sales_growth,
            "expenses"            => $expenses,
            "sales_growth"        => $sales_growth,
            "expenses"            => $expenses,
            "sales_growth"        => $sales_growth,
            "expenses"            => $expenses,
            "balanceStatus"       => $balanceStatus,
            "payment_gateways"    => $payment_gateways,
            "money_history"       => $money_history,
            "transaction_history" => $transaction_history,
        ], "Data fetched successfully", 200);
    }

}

<?php
namespace App\Services;

use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\User;
use App\Services\UserService;
use App\Helpers\Accounts\Account;


class AnalyticsService extends BaseService
{

    public function __construct(
        private readonly UserService $userService,
        private readonly StoreFileResource $storeFileResource,
        private readonly ResponseHelper $responseHelper,
        private readonly ProjectService $projectService,
        private readonly AppService $appService,
        private readonly EcommerceService $ecommerceService,
        private readonly CRMService $crmService,
        private readonly WalletService $walletService,
        private readonly SaleService $salesService,
        private readonly ExpenseService $expenseService,
        private readonly RevenueService $revenueService,
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }

    public function index()
    {

        $data = $this->appService->getAppData();

        return $this->responseHelper->respond(['data' => $data], "Data fetched successfully", 200);
    }

    public function permissions()
    {
        $permissions = [];

        $accountManager  = new Account($this->user());
        $account_permissions = $accountManager->authenticatedUserPermissions();

        return $this->responseHelper->respond(['permissions' =>  $account_permissions], "Permissions fetched successfully", 200);
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

        // $this->userService = new UserService($this->account(), $this->user());
        $activeUsers    = $this->userService->activeUsers();
        $user_growth    = $this->userService->getUsersGrowth();
        $viewsPerMinute = $this->userService->getViewsPerMinute();
        $viewsPerMonth  = $this->userService->getViewsPerMonth();

        $viewsGrowth                = $this->userService->getViewsGrowth();
        $top_pages                  = $this->userService->getTopPages();
        $top_pages_with_bounce_rate = $this->userService->getTopPages();
        $page_bounce_rate           = $this->userService->getTopPagesByBounceRate();
        $engagementOverview         = $this->userService->getEngagementOverview();
        $media_traffic              = $this->userService->getUsersTraffic();
        $channel_visits             = $this->userService->getChannelVisits();
        $country_sessions           = $this->userService->getUsersTrafficByCountry();

        $os_sessions = $this->userService->getChannelVisitsByOS();

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

        $total_projects = $this->projectService->getProjectsCount();

        $total_tasks = $this->projectService->getTasksCount();

        $project_team_members = $this->projectService->getProjectTeamMembers();
        $tasks_status         = $this->projectService->getTasksStatus();
        $finishedTasks        = $this->projectService->getFinishedTasksCount();
        $tasksDueToday        = $this->projectService->getTasksDueToday();
        $tasksDueTomorrow     = $this->projectService->tasksDueTomorrow();
        $tasksDueThisWeek     = $this->projectService->tasksDueThisWeek();
        $tasksDueThisMonth    = $this->projectService->tasksDueThisMonth();
        $productivity         = $this->projectService->calculateProductivity();
        $recent_activity      = $this->projectService->recentActivities();

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

        $customers        = $this->ecommerceService->getCustomersCount();
        $customers_growth = $this->ecommerceService->getCustomersGrowth();

        $orders = $this->ecommerceService->getOrders();

        $orders_growth = $this->ecommerceService->getOrdersGrowth();

        $sales_growth         = $this->ecommerceService->salesGrowth();
        $monthly_sales_growth = $this->ecommerceService->monthlySalesGrowth();

        $revenue_growth = $this->ecommerceService->getRevenueGrowth();

        $monthly_revenue        = $this->ecommerceService->getRevenuePerMonth();
        $previous_month_revenue = $this->ecommerceService->getPreviousMonthRevenue();

        $locations_revenue  = $this->ecommerceService->getRevenuePerLocation();
        $total_days_revenue = $this->ecommerceService->getTotalDaysRevenue();

        // Top Selling Products

        $top_products = $this->ecommerceService->getTopSellingProducts();
        // Total Sales

        $saleTypes = $this->ecommerceService->getSaleTypes();

        // Recent Activity

        $recent_activity = $this->ecommerceService->getRecentActivity();

        //graph data
        $graph_data = $this->ecommerceService->getGraphData();

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
        $campaigns_sent   = $this->crmService->getCampaignsCount();
        $campaigns_growth = $this->crmService->getCampaignsGrowth();

        // New Leads
        $newLeads       = $this->crmService->getNewLeadsCount();
        $newLeadsGrowth = $this->crmService->getLeadsGrowth();

        //deals
        $campaigns_deals = $this->crmService->getDeals();

        $campaigns_deals_growth = $this->crmService->getDealsGrowth();

        //booked revenue
        $campaigns_booked_revenue        = $this->crmService->getBookedRevenue();
        $campaigns_booked_revenue_growth = $this->crmService->getBookedRevenueGrowth();

        // Campaigns stats
        //   Totals,  Total Sent, Reached, Opened
        $campaign_statistics = $this->crmService->getCampaignsState();

        //monthly revenue growth
        $campaigns_month_revenue          = $this->crmService->getMonthlyBookedRevenue();
        $campaigns_previous_month_revenue = $this->crmService->getMonthlyBookedRevenue($month = date('m') - 1);

        // Top Performing
        $top_campaigners = $this->crmService->getTopCampaigners();

        // Recent Leads
        $recent_leads = $this->crmService->getRecentLeads();

        // Todos
        $todos = $this->crmService->getTodos();

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

        $wallets = $this->walletService->getWallets();
        // Sales

        $sales        = $this->salesService->getAllSales();
        $sales_growth = $this->salesService->getSalesGrowth();

        // Expenses
        $expenses     = $this->expenseService->getExpenses();
        $sales_growth = $this->expenseService->getExpensesGrowth();
        // Revenue

        $expenses     = $this->revenueService->getRevenue();
        $sales_growth = $this->revenueService->getRevenueGrowth();

        // Currencies Used
        // Ksh

        // $expenses = $this->walletService->getCurrencies();

        //  BalanceStatus
        $balanceStatus = $this->walletService->getBalanceStatus();

        //  Graph database_path('')
        //  Based on transactions

        //  Account Suggestions
        //  Payment Methods
        $payment_gateways = $this->walletService->getTransactionGateways();

        //  Money History
        //  Income
        //  Expenses
        // Transfar

        $money_history = $this->walletService->getMoneyHistory();

        //   Transaction history

        $transaction_history = $this->walletService->getTransactionHistory();

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

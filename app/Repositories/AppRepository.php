<?php
namespace App\Repositories;

use App\Http\Controllers\BaseController;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\User;
use App\Repositories\EmployeesRepository;
use App\Repositories\ExpenseRepository;
use App\Repositories\LoansRepository;
use App\Repositories\OrdersRepository;
use App\Repositories\ProfitRepository;
use App\Repositories\RequiredItemsRepository;
use App\Repositories\RevenueRepository;
use App\Repositories\SalesRepository;
use App\Repositories\StockRepository;
use App\Repositories\SuppliesRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppRepository
{

    public $ecommerce_models = [
        'sales' => Sale::class,
        'stock' => Stock::class,
    ];

    public $office_models = [
        'sales' => Sale::class,
        'stock' => Stock::class,
    ];
    public const noprofile = "https://storage.googleapis.com/dukaverse-e4f47.appspot.com/app/noprofile.png";
    public const nofile    = "https://storage.googleapis.com/dukaverse-e4f47.appspot.com/app/nofile.png";
    //get ip address
    public function getIp()
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $_SERVER;
                    }
                }
            } else {
                return "unknown";
            }
        }
    }
    public function getLocation()
    {
        # code...
        $currentUserInfo = null;
        return $currentUserInfo;
    }
    public function getBaseImages()
    {

        $images = [
            "noprofile" => "https://storage.googleapis.com/dukaverse-e4f47.appspot.com/app/noprofile.png",
            "nofile"    => "https://storage.googleapis.com/dukaverse-e4f47.appspot.com/app/nofile.png",
        ];
        return $images;
    }

    public function getRegisteredMonths()
    {
        # code...
        $registrationmonths = User::distinct('month')->orderBy('month', 'ASC')->get('month');
        return $registrationmonths;
    }

    public function getMonthlyUsers($month)
    {
        # code...
        $users = User::where('month', $month)->get();
        return $users;
    }

    public function getRevenue($account, $month)
    {
        # code...
        $revenueRepo = new SalesRepository($account);
        $revenue     = $revenueRepo->getRevenue("month", $month);
        return $revenue;
    }

    public function getExpenses($account, $month)
    {
        # code...
        $expenseRepo = new ExpenseRepository($account);
        $expenses    = $expenseRepo->getExpenses($account, $month);
        return $expenses;
    }

    public function getProfit($account, $month)
    {
        # code...
        $profit = 0;
        if ($this->getRevenue($account, $month) && $this->getExpenses($account, $month)) {
            $profit = $this->getRevenue($account, $month) - $this->getExpenses($account, $month);
        }
        return $profit;
    }

    public function getMonthlyProfit($repo, $month = null, $year = null)
    {
        # code...
        $profitdata = $repo->getProfit($month, $year);
        return $profitdata->sum('profit_amount');
    }

    public function getRevenueGrowth($account, $month)
    {
        # code...
        $thisMonthRev     = $this->getRevenue($account, $month);
        $lastMonthRev     = $this->getRevenue($account, $month - 1);
        $growth           = $thisMonthRev - $lastMonthRev;
        $growthPercentile = ($growth / $thisMonthRev) * 100;

        return $growthPercentile;
    }

    public function getExpenseGrowth($account, $month)
    {
        # code...
        $thisMonthEx      = $this->getExpenses($account, $month);
        $lastMonthEx      = $this->getExpenses($account, $month - 1);
        $growth           = $thisMonthEx - $lastMonthEx;
        $growthPercentile = ($growth / $thisMonthEx) * 100;

        return $growthPercentile;
    }

    public function getProfitGrowth($account, $month)
    {
        $thisMonthProfit  = $this->getProfit($account, $month);
        $lastMonthProfit  = $this->getProfit($account, $month - 1);
        $growth           = $thisMonthProfit - $lastMonthProfit;
        $growthPercentile = ($growth / $thisMonthProfit) * 100;

        return $growthPercentile;
    }

    public function getAppData()
    {
        # code...
        $baseController = new BaseController();
        $account        = $baseController->account();
        $user           = User::where('id', Auth::id())->first();

        //

        if (! $account) {
            return false;
        }

        $expenseRepo       = new ExpenseRepository($account);
        $salesRepo         = new SalesRepository($account);
        $revenueRepo       = new RevenueRepository($account);
        $profitRepo        = new ProfitRepository($account);
        $stockRepo         = new StockRepository($account);
        $requiredItemsRepo = new RequiredItemsRepository($account);
        $orderRepo         = new OrdersRepository($account);
        $employeeRepo      = new EmployeesRepository($account);
        $suppliesrRepo     = new SuppliesRepository($account);
        $loansRepo         = new LoansRepository($account);
        $customerRepo      = new CustomersRepository($account);

        $sales = $salesRepo->getTopPerformingItems();

        $dates = null;
        try {
            $dates = $account->items()
                ->select(DB::raw('YEAR(created_at) year, MONTH(created_at) month, MONTHNAME(created_at) month_name'))
                ->distinct()
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
        } catch (Exception $e) {
            $e->getMessage();
        }

        //linechart data
        $data = [];
        //dd($dates);

        //set months
        $data['months'] = $this->getMonths($dates);

        //piechart data
        $salesPData   = [];
        $expensePData = [];
        $revenuePData = [];
        $stockPData   = [];
        $loansPData   = [];

        $salesData   = [];
        $expenseData = [];
        $revenueData = [];
        $stockData   = [];
        $loansData   = [];

        foreach ($dates as $date) {
            $month     = $date->month;
            $monthName = $date->month_name;
            $sales     = $this->getMonthlySales($salesRepo, $month);

            $expenses = $this->getMonthlyExpense($expenseRepo, $month);
            $revenue  = $this->getMonthlyRevenue($revenueRepo, $month);
            $stock    = count($stockRepo->getDisctictStockItems($month));
            $loans    = $this->getLoanApplications($loansRepo, $month);

            //set piechart data
            $salesPData[]   = $this->pieChartData($sales, $monthName);
            $expensePData[] = $this->pieChartData($expenses, $monthName);
            $revenuePData[] = $this->pieChartData($revenue, $monthName);
            $stockPData[]   = $this->pieChartData($stock, $monthName);
            $loansPData[]   = $this->pieChartData($loans, $monthName);

            //linechart data
            $salesData[]   = $sales;
            $expenseData[] = $expenses;
            $revenueData[] = $revenue;
            $stockData[]   = $stock;
            $loansData[]   = $loans;
        }

        $customers['recent_customers'] = $customerRepo->recentCustomers();

        //linechart data
        $data['salesData']   = $salesData;
        $data['expenseData'] = $expenseData;
        $data['revenueData'] = $revenueData;
        $data['stockData']   = $stockData;
        $data['loansData']   = $loansData;

        //piechart data

        $data['salesPData']   = $salesPData;
        $data['expensePData'] = $expensePData;
        $data['revenuePData'] = $revenuePData;
        $data['stockPData']   = $stockPData;
        $data['loansPData']   = $loansPData;

        $data['sales_value']                  = $this->getMonthlySales($salesRepo);
        $data['expenses_value']               = $this->getMonthlyExpense($expenseRepo, date("m"));
        $data['revenue_value']                = $this->getMonthlyRevenue($revenueRepo, date("m"));
        $data['previous_month_revenue_value'] = $this->getMonthlyRevenue($revenueRepo, date("m") - 1);
        $data['profit_value']                 = $this->getMonthlyProfit($profitRepo, date("m"));

        $data['sales_growth']    = $salesRepo->getSalesGrowth();
        $data['expenses_growth'] = $expenseRepo->getExpensesGrowth();
        $data['revenue_growth']  = $revenueRepo->getRevenueGrowth();
        $data['profit_growth']   = $profitRepo->getProfitGrowth();

        $data['sold_items']     = $salesRepo->getSoldItems();
        $data['stock']          = count($stockRepo->getDisctictStock());
        $data['required_items'] = count($requiredItemsRepo->getAllRequiredItems());
        $data['ordered_items']  = count($orderRepo->getAllorders());

        $data['employees'] = count($employeeRepo->getEmployees());
        $data['supplies']  = count($suppliesrRepo->getAllSupplies());
        $data['orders']    = $orderRepo->getOrdersCount();
        $data['loans']     = count($loansRepo->getLoanApplications());

        $data['account']          = $account;
        $data['account_settings'] = $account->saleSetting;

        $data['base_images']          = $this->getBaseImages();
        $data['currency']             = $account->currency ?? "ksh";
        $data['top_performing']       = $salesRepo->getTopPerformingItems();
        $data['customers']            = $customers;
        $data['accountNotifications'] = $account->unreadNotifications;

        $data['userNotifications'] = $user->unreadNotifications();

        return $data;
    }

    public function getMonths($periods)
    {

        $months = [
        ];
        foreach ($periods as $period) {
            $month = $period->month_name;
            array_push($months, $month);
        }
        return $months;
    }

    public function getLoanApplications($loansRepo, $month)
    {

        // $loans = array();
        $loan = $loansRepo->getAppliedLoans($month, null)->sum("loan_amount");
        //array_push($loans, $loan);
        return $loan;
    }

    public function getMonthlyRevenue($revenueRepo, $period)
    {
        $revenue = $revenueRepo->getMonthlyRevenue($period)->sum('revenue');
        return $revenue;
    }

    public function getMonthlyExpense($expenseRepo, $period)
    {

        $expense = $expenseRepo->getMonthlyExpenses($period)->sum('expense');

        return $expense;
    }

    public function getMonthlySales($salesRepo = null, $period = null)
    {
        $sale = $salesRepo->getMonthlySales($period)->sum('paid_amount');
        return $sale;
    }

    //sets pie chart data
    public function pieChartData($data, $month)
    {
        $pdata = [];
        # code...
        $color = $this->getColor();
        $value = $data;

        // $value = 20;
        $highlight = $this->getColor();
        $label     = $month;

        $pdata['color']     = $color;
        $pdata['value']     = $value;
        $pdata['highlight'] = $highlight;
        $pdata['label']     = $label;
        return $pdata;
    }

    //gets random color value
    public function getColor()
    {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    public function getaccountEmployeeRoles()
    {
        # code...
        $roles = [
            "Sales"      => "Sales",
            "Managerial" => "Managerial",
            "Accounts"   => "Accounts",
            "Other"      => "Other",
        ];
        return $roles;
    }

}

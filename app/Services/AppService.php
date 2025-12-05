<?php
namespace App\Services;

use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\User;
use App\Services\ExpenseService;
use App\Services\LoansService;
use App\Services\OrderService;
use App\Services\ProfitService;
use App\Services\RequiredItemService;
use App\Services\RevenueService;
use App\Services\SaleService;
use App\Services\StockService;
use App\Services\SuppliesService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppService extends BaseService
{

    public function __construct(
        private readonly UserService $userService,
        private readonly StoreFileResource $storeFileResource,
        private readonly ResponseHelper $responseHelper,
        private readonly SaleService $salesService,
        private readonly ExpenseService $expenseService,
        private readonly RevenueService $revenueService,
        private readonly ProfitService $profitService,
        private readonly StockService $stockService,
        private readonly RequiredItemService $RequiredItemService,
        private readonly OrderService $orderService,
        private readonly EmployeeService $employeeService,
        private readonly SuppliesService $suppliesService,
        private readonly LoansService $loansService,
        private readonly CustomerService $customerService,
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }

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

    public function getLocationDetails()
    {
        # code...
        $region = $this->getLocation();
        if (! $region) {
            $region = [
                "ip"          => "unknown",
                "countryName" => "Kenya",
                "countryCode" => "KE",
                "regionCode"  => "30",
                "regionName"  => "Nairobi Province",
                "cityName"    => "Nairobi",
                "zipCode"     => null,
                "isoCode"     => null,
                "postalCode"  => null,
                "latitude"    => "-1.2841",
                "longitude"   => "36.8155",
                "metroCode"   => null,
                "areaCode"    => "",
                "timezone"    => "Africa/Nairobi",
                "driver"      => "Stevebauman\Location\Drivers\GeoPlugin",
            ];
        }

        // $phoneCode = CountriesList::where('iso', "KE")->first()->phonecode;

        // $region['phoneCode'] = $phoneCode;
        // $region['countries'] = CountriesList::all();
        return $region;
    }

    public function getBaseImages()
    {

        $images = [
            "noprofile" => "https://storage.googleapis.com/dukaverse-e4f47.appspot.com/app/noprofile.png",
            "nofile"    => "https://storage.googleapis.com/dukaverse-e4f47.appspot.com/app/nofile.png",
        ];
        return $images;
    }

    public function getNoProfileImage()
    {
        $baseImages = $this->getBaseImages();
        return $baseImages['noprofile'];
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
        $revenue = $this->salesService->getRevenue("month", $month);
        return $revenue;
    }

    public function getExpenses($account, $month)
    {
        # code...
        $expenses = $this->expenseService->getExpenses($account, $month);
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
        $account = $this->account;
        $user    = User::where('id', Auth::id())->first();

        //

        if (! $account) {
            return false;
        }

        $sales = $this->salesService->getTopPerformingItems();

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
            $sales     = $this->getMonthlySales($this->salesService, $month);

            $expenses = $this->getMonthlyExpense($this->expenseService, $month);
            $revenue  = $this->getMonthlyRevenue($this->revenueService, $month);
            $stock    = count($this->stockService->getDisctictStockItems($month));
            $loans    = $this->getLoanApplications($this->loansService, $month);

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

        $customers['recent_customers'] = $this->customerService->recentCustomers();

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

        $data['sales_value']                  = $this->getMonthlySales($this->salesService);
        $data['expenses_value']               = $this->getMonthlyExpense($this->expenseService, date("m"));
        $data['revenue_value']                = $this->getMonthlyRevenue($this->revenueService, date("m"));
        $data['previous_month_revenue_value'] = $this->getMonthlyRevenue($this->revenueService, date("m") - 1);
        $data['profit_value']                 = $this->getMonthlyProfit($this->profitService, date("m"));

        $data['sales_growth']    = $this->salesService->getSalesGrowth();
        $data['expenses_growth'] = $this->expenseService->getExpensesGrowth();
        $data['revenue_growth']  = $this->revenueService->getRevenueGrowth();
        $data['profit_growth']   = $this->profitService->getProfitGrowth();

        $data['sold_items']     = $this->salesService->getSoldItems();
        $data['stock']          = count($this->stockService->getDisctictStock());
        $data['required_items'] = count($this->RequiredItemService->getAllRequiredItems());
        $data['ordered_items']  = count($this->orderService->getAllorders());

        $data['employees'] = count($this->employeeService->getEmployees());
        $data['supplies']  = count($this->suppliesService->getAllSupplies());
        $data['orders']    = $this->orderService->getOrdersCount();
        $data['loans']     = count($this->loansService->getLoanApplications());

        $data['account']          = $account;
        $data['account_settings'] = $account->saleSetting;

        $data['base_images']          = $this->getBaseImages();
        $data['currency']             = $account->currency ?? "ksh";
        $data['top_performing']       = $this->salesService->getTopPerformingItems();
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

    public function getLoanApplications($loansService, $month)
    {

        // $loans = array();
        $loan = $loansService->getAppliedLoans($month, null)->sum("loan_amount");
        //array_push($loans, $loan);
        return $loan;
    }

    public function getMonthlyRevenue($revenueService, $period)
    {
        $revenue = $revenueService->getMonthlyRevenue($period)->sum('revenue');
        return $revenue;
    }

    public function getMonthlyExpense($expenseService, $period)
    {

        $expense = $expenseService->getMonthlyExpenses($period)->sum('expense');

        return $expense;
    }

    public function getMonthlySales($salesService = null, $period = null)
    {
        $sale = $this->salesService->getMonthlySales($period)->sum('paid_amount');
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

<?php
namespace App\Repositories;

use App\Helpers\notifications\DukaverseNotification;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class EcommerceRepository
{

    private $user;
    private $account;
    public function __construct($account, $user = null)
    {
        $this->user    = $user != null ? $user : auth()->user();
        $this->account = $account;

    }

    public function ecommerces()
    {
        $retails = $this->user->ecommerces()->get();
        return $retails;
    }

    public function getPaymentPreferences()
    {
        # code...

        $paymentPref = [
            "mpesapaybill" => "mpesapaybill",
            "mpesatill"    => "mpesatill",
            "dukaverse"    => "dukaverse",
        ];

        return $paymentPref;
    }

    public static function checkDueNotifications($account)
    {

        $timezone = env('timezone');

        $notification_required = false;

        $account = $account::where('is_active', true)
            ->with('settings')
            ->get();

        $settings = $account
            ->pluck('settings')
            ->flatten();

        if ($settings->timezone) {
            $timezone = $settings->timezone;
        }

        foreach ($settings as $setting) {
            $notification_schedules = $setting->notificationTime;

            $now                   = Carbon::now();
            $notification_required = new DukaverseNotification();

            if (count($notification_schedules) > 0) {
                foreach ($notification_schedules as $notification_schedule) {

                    if ($notification_schedule &&
                        $notification_schedule->time->between(Carbon::today($timezone)->setTime(21, 0), Carbon::today($timezone)->setTime(22, 30))
                    ) {

                        $notification_required = new DukaverseNotification($notification_schedule->time,
                            $notification_schedule->time,
                            "New Message");

                    }
                }
            }
        }

        return $notification_required;
    }

    public function getCustomers()
    {
        $customerRepository = new CustomersRepository($this->account);
        $customers          = $customerRepository->getCustomers();
        return $customers;
    }

    public function getCustomersCount()
    {
        $customers = $this->account->customers()->count();
        return $customers;
    }

    public function getCustomersGrowth()
    {

        $customerRepository = new CustomersRepository($this->account);
        $customers_growth   = $customerRepository->getCustomerGrowth();
        return $customers_growth;

    }

    public function getOrders()
    {

        $orders = $this->account->orders()->count();

        return $orders;

    }

    public function getGraphData()
    {
        $salesRepo   = new SalesRepository($this->account);
        $revenueRepo = new RevenueRepository($this->account);

        $dates = null;
        try {
            $dates = $this->account->items()
                ->select(DB::raw('YEAR(created_at) year, MONTH(created_at) month, MONTHNAME(created_at) month_name'))
                ->distinct()
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
        } catch (Exception $e) {
            $e->getMessage();
        }

        foreach ($dates as $date) {
            $month     = $date->month;
            $monthName = $date->month_name;
            $sales     = $this->getMonthlySales($salesRepo, $month);

            $revenue = $this->getMonthlyRevenue($revenueRepo, $month);

            //set piechart data
            $salesPData[]   = $this->pieChartData($sales, $monthName);
            $revenuePData[] = $this->pieChartData($revenue, $monthName);

            //linechart data
            $salesData[]   = $sales;
            $revenueData[] = $revenue;
        }

    }

    public function getMonthlySales($salesRepo = null, $period = null)
    {
        $sale = $salesRepo->getMonthlySales($period)->sum('paid_amount');
        return $sale;
    }

    public function getMonthlyRevenue($revenueRepo, $period)
    {
        $revenue = $revenueRepo->getMonthlyRevenue($period)->sum('revenue');
        return $revenue;
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

    public function getOrdersGrowth()
    {

        $ordersRepository = new OrdersRepository($this->account);
        $orders_growth    = $ordersRepository->getOrdersGrowth();
        return $orders_growth;

    }

    public function getRevenue()
    {

        $revenueRepository = new RevenueRepository($this->account);
        $revenue           = $revenueRepository->getRevenue();
        return $revenue;

    }

    public function getRevenueGrowth()
    {

        $revenueRepository = new RevenueRepository($this->account);
        $revenue           = $revenueRepository->getRevenueGrowth();
        return $revenue;

    }

    public function monthlyProjectionsVsActualSales()
    {

    }

    public function salesGrowth()
    {

        $salesRepository = new SalesRepository($this->account);
        $salesGrowth     = $salesRepository->getSalesGrowth();
        return $salesGrowth;

    }

    public function monthlySalesGrowth()
    {

        $salesRepository = new SalesRepository($this->account);
        $salesGrowth     = $salesRepository->getSalesGrowth();
        return $salesGrowth;

    }

    public function getRevenuePerWeek()
    {

        $revenueRepository = new RevenueRepository($this->account);
        $revenue           = $revenueRepository->getRevenueGrowth();
        return $revenue;

    }

    public function getRevenuePerMonth()
    {

        $revenueRepository = new RevenueRepository($this->account);
        $month             = date('m');

        $revenue = $revenueRepository->getAllRevenue(month: $month - 1);
        return $revenue;

    }

    public function getPreviousMonthRevenue()
    {

        $revenueRepository = new RevenueRepository($this->account);
        $month             = date('m');

        $revenue = $revenueRepository->getAllRevenue(month: $month - 1);
        return $revenue;

    }

    public function getTotalDaysRevenue()
    {

        $revenueRepository = new RevenueRepository($this->account);
        $revenue           = $revenueRepository->getTotalDaysRevenue();
        return $revenue;

    }

    public function getRevenuePerLocation()
    {
        $ordersRepository              = new OrdersRepository($this->account);
        $revenue                       = [];
        $revenue['sales_per_country']  = $ordersRepository->getOrdersRevenuePerCountry();
        $revenue['sales_per_location'] = $ordersRepository->getOrdersRevenuePerLocation();

        return $revenue;
    }

    public function getTopSellingProducts()
    {

        $sales_repository = new SalesRepository($this->account);

        $top_sales = $sales_repository->getTopPerformingItems();

        return $top_sales;

    }

    public function getSaleTypes()
    {

        $sale_types = $this->account->sales()->select('retail_item_id',
            DB::raw('count(id) as total_sales'),
            DB::raw('sum(selling_price) as expected_revenue'))
            ->groupBy('sale_type');
        return $sale_types;
    }

    public function getRecentActivity($limit = 20)
    {
        $recent_activities = $this->account->notifications()
            ->where('type', 'sales')
            ->orWhere('type', 'products')
            ->orWhere('type', 'orders')
            ->get();

        return $recent_activities;
    }

}

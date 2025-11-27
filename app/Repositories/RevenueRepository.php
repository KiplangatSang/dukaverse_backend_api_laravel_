<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RevenueRepository
{
    private $account;
    protected $month, $year, $revenue = null, $lastRevenue = null, $revenue_id = null, $result = null;
    public function __construct($account)
    {
        $this->account = $account;
        $this->month   = date('M');
        $this->year    = date("Y");
    }

    public function saveRevenue($revenue)
    {

        // get last revenues
        $monthlyRevenue = $this->account->revenues()
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->first();

        // dd($monthlyRevenue);
        // dd($this->account);
        $this->revenue_id = "REV" . Str::random(5);
        if ($monthlyRevenue) {
            $this->revenue    = $monthlyRevenue->revenue + $revenue;
            $this->revenue_id = $monthlyRevenue->revenue_id;
        } else {
            $this->revenue = $revenue;
        }

        //save revenues by locking db
        try {
            $month = $this->month;
            $year  = $this->year;
            DB::transaction(function () use ($month, $year) {
                // dd($this->revenue_id);
                $this->result = $this->account->revenues()->updateOrCreate(
                    [
                        "month"      => $month,
                        "year"       => $year,
                        "revenue_id" => $this->revenue_id,
                    ],
                    [

                        "revenue" => $this->revenue,
                    ]
                );
            }, 3);
            DB::commit();

            return $this->result;
        } catch (\PDOException $e) {
            // Woopsy
            DB::rollBack();
            return false;
        }

        return $this->result;
    }

    //get employee sales
    public function getRevenuesByDate($startDate, $endDate)
    {
        $revenues = $this->account->revenues()->whereBetween('created_at', [$startDate . " 00:00:00", $endDate . " 23:59:59"])->get();
        return $revenues;
    }

    public function getAllRevenue($month = null, $year = null)
    {
        $year     = date("Y");
        $revenues = null;
        if ($month) {
            $revenues = $this->account->revenues()
                ->whereMonth("created_at", $month)
                ->whereYear('created_at', '=', $year)
                ->get();
        } else {
            $revenues = $this->account->revenues()
                ->whereYear('created_at', '=', $year)
                ->get();
        }

        $totalrevenues = $revenues->sum('revenue');
        # code...
        return $totalrevenues;
    }

    public function getTotalDaysRevenue()
    {

        $today    = now()->format("YY-m-d");
        $revenues = $this->account->revenues()
            ->whereBetween('created_at', [$today . " 00:00:00", $today . " 23:59:59"])
            ->sum('revenue');

        $totalrevenues = $revenues;
        return $totalrevenues;
    }

    public function getRevenue($month = null, $year = null)
    {
        if (! $year) {
            $year = date("Y");
        }

        $revenues = null;
        if ($month) {
            $revenues = $this->account->revenues()
                ->whereMonth("created_at", $month)
                ->whereYear('created_at', '=', $year)
                ->get();
        } else {
            $revenues = $this->account->revenues()
                ->whereYear('created_at', '=', $year)
                ->get();
        }

        return $revenues;
    }

    public function getMonthlyRevenue($month = null, $year = null)
    {
        if (! $year) {
            $year = date('Y');
        }

        $revenues = null;

        if ($month) {
            $revenues = $this->account->revenues()
                ->whereMonth('created_at', '=', $month)
                ->whereYear('created_at', '=', $year)
                ->get();
        } else {
            $revenues = $this->account->revenues()
            // ->whereYear('created_at', '=', $year)
                ->get();
        }

        # code...
        return $revenues;
    }

    public function getRevenueGrowth($month = null)
    {

        if (! $month) {
            $month = date('m');
        }

        $currentRevenue  = $this->getAllRevenue($month);
        $previousRevenue = $this->getAllRevenue($month - 1);

        //dd("$currentRevenue");

        if ($previousRevenue <= 0) {
            $previousRevenue = 1;
        }

        if ($currentRevenue <= 0) {
            $currentRevenue = 1;
        }

        $growth = (($currentRevenue - $previousRevenue) / $currentRevenue) * 100;
        $growth = number_format($growth, 2);

        return $growth;
    }
}

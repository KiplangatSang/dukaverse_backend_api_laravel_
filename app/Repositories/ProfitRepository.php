<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfitRepository
{
    private $account;
    protected $month, $year, $expense = null, $result = null, $profit_id = null, $profit = null;

    public function __construct($account)
    {
        $this->account = $account;
        $this->month = date('M');
        $this->year = date("Y");
    }


    public function setProfitFromExpense($expense)
    {
        # code...

        $lastProfit =  $this->account->profit()
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->first();

        if ($lastProfit)
            $this->profit = $lastProfit->profit_amount  - $expense;
        else
            $this->profit = $expense;
        return $this->setProfit($this->profit);
    }

    public function setProfitFromRevenue($revenue)
    {
        # code...
        $lastProfit =  $this->account->profit()
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->first();

        if ($lastProfit)
            $this->profit = $lastProfit->profit_amount + $revenue;
        else
            $this->profit = $revenue;
        return $this->setProfit($this->profit);
    }
    public function setProfit($profit)
    {
        $this->profit = $profit;

        // get last profit
        $lastProfit =  $this->account->profit()
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->first();

        //set profit string
        if ($lastProfit)
            $this->profit_id = $lastProfit->profit_id;
        else
            $this->profit_id = "PROF" . Str::random(4);

        //save expense by locking db
        DB::transaction(function () {
            $this->result =  $this->account->profit()->updateOrCreate(
                [
                    "month" => $this->month,
                    "year" => $this->year,
                    "profit_id" => $this->profit_id,
                ],
                [

                    "profit_amount" => $this->profit,
                ]
            );
        }, 3);

        return  $this->result;
    }

    public function getProfit($month = null, $year = null)
    {
        if (!$year)
            $year = date("Y");
        $profit = 0;
        if ($month) {
            $profit = $this->account->profit()
                ->whereMonth('created_at', '=', $month)
                ->whereYear('created_at', '=', $year)
                ->get();
        } else
            $profit = $this->account->profit()
                ->whereYear('created_at', '=', $year)
                ->get();

        return $profit;
    }


    public function getProfitGrowth($month = null)
    {
        if (!$month)
            $month = date('m');


        $currentProfit = $this->getProfit($month)->sum('profit_amount');
        $previousProfit =   $this->getProfit($month - 1)->sum('profit_amount');


        if ($previousProfit <= 0)
            $previousProfit = 1;

        if ($currentProfit <= 0)
            $currentProfit = 1;

        $growth = (($currentProfit -  $previousProfit) / $currentProfit) * 100;
        $growth = number_format($growth, 2);
        return $growth;
    }
}

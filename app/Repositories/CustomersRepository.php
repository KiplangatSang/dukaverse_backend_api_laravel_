<?php
namespace App\Repositories;

class CustomersRepository
{
    private $account;
    public function __construct($account)
    {
        $this->account = $account;
    }
    public function getDisctictCustomers()
    {
        $customers = $this->account->customers()->distinct('email')->get();

        return $customers;
    }
    public function recentCustomers()
    {
        $customers = $this->account->customers()->latest()->get();
        return $customers;
    }
    public function getAllCustomers()
    {
        $customers = $this->account->customers()->get();
        return $customers;
    }

    //get sale by item id
    public function getCustomerById($id)
    {
        $customer = $this->account
            ->customers()
            ->where('id', $id)
            ->with('saletransactions')
            ->with('credits')
            ->first();

        return $customer;
    }

    //get employee sales
    public function getCustomersCredit($id)
    {
        $credits = $this->account->customerCredits()
            ->where('id', $id)
            ->with('customer')
            ->with('saleTransaction.sales.items')
            ->first();

        return $credits;
    }

    //get employee sales
    public function getCustomersByDate($startDate, $endDate)
    {
        $order = $this->account->customers()->whereBetween('created_at', [$startDate . " 00:00:00", $endDate . " 23:59:59"])->get();
        return $order;
    }

    public function getCustomersCost()
    {
        $creditPrice = $this->account->customers()->sum('creditPrice');
        # code...
        return $creditPrice;
    }

    public function getCustomers($month = null, $year = null)
    {

        if (! $year) {
            $year = date("Y");
        }
        $customers = null;
        if ($month) {
            $customers = $this->account->customers()
                ->whereMonth("created_at", $month)
                ->whereYear('created_at', '=', $year)
                ->get();
        } else {
            $customers = $this->account->customers()
            // ->whereYear('created_at', '=', $year)
                ->get();
        }

        //dd($sales);
        return $customers;
    }

    public function getCustomersByKeys($key, $value)
    {

        $customers = null;
        $customers = $this->account->customers()->where($key, $value)->orderBy('created_at', 'DESC')->get();
        return $customers;
    }

    public function getCreditedItems()
    {
        $creditedItems = $this->account->sales()->where('on_credit', true)->get();

        return $creditedItems;
    }

    public function getCustomerGrowth($month = null)
    {

        if (! $month) {
            $month = date('m');
        }

        $currentCustomers  = count($this->getCustomers($month));
        $previousCustomers = count($this->getCustomers($month - 1));

        //dd("$currentRevenue");

        if ($currentCustomers <= 0) {
            $previousCustomers = 1;
            $currentCustomers  = 1;
        }

        $growth = (($currentCustomers - $previousCustomers) / $currentCustomers) * 100;
        $growth = number_format($growth, 2);

        return $growth;
    }
}

<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class WalletRepository
{

    private $account;
    public function __construct($account)
    {
        $this->account = $account;
    }

    public function getWallets()
    {

        return $this->account->wallets;
    }

    public function getSales()
    {
        $sales_repository = new SalesRepository($this->account);
        $sales            = $sales_repository->getMonthlySales();

        return $sales;
    }

    public function getSalesGrowth()
    {
        $sales_repository = new SalesRepository($this->account);
        $salesGrowth      = $sales_repository->getSalesGrowth();
        return $salesGrowth;
    }

    public function getExpenses()
    {

        $expense_repository = new ExpenseRepository($this->account);
        $expenses           = $expense_repository->getExpenses();
        return $expenses;

    }

    public function getExpensesGrowth()
    {

        $expense_repository = new ExpenseRepository($this->account);
        $expense_growth     = $expense_repository->getExpensesGrowth();
        return $expense_growth;

    }
    public function getRevenue()
    {

        $revenue_repository = new RevenueRepository($this->account);
        $revenue            = $revenue_repository->getRevenue();
        return $revenue;

    }

    public function getRevenueGrowth()
    {

        $revenue_repository = new RevenueRepository($this->account);
        $revenue_growth     = $revenue_repository->getRevenueGrowth();
        return $revenue_growth;

    }

    public function getCurrencies()
    {

        $currencies = $this->account->saleSetting()->get('currency');

        return $currencies;
    }

    public function getBalanceStatus()
    {
        // implement logic here based on transactions
        $balanceStatus = $this->account->transactions()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(balance) as daily_balance')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return $balanceStatus;

    }

    public function getMoneyHistory()
    {

//          Income

        $revenue = $this->getRevenue();
//  Expenses

        $expenses = $this->getExpenses();

// Transfer
        $transfer = $this->getExpenses();

        return [
            "revenue"  => $revenue,
            "expenses" => $expenses,
            "transfer" => $transfer,
        ];

    }

    public function getTransactionGateways()
    {
        //  Merchant List(Payment Methods)

        $gateways = $this->account->paymentGateways()->get();
        return $gateways;
    }

    public function getTransactionHistory()
    {
        //  Merchant List(Payment Methods)

        $transactions = $this->account->transactions()->get();
        return $transactions;
    }

}

<?php
namespace App\Repositories;

use App\Http\Controllers\Retailer\SaleTransactionController;
use App\Http\Requests\StoreSaleTransactionRequest;
use App\Models\Retail;
use App\Models\Sale;
use App\Models\SaleTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesRepository extends BaseRepository
{
    protected $currentAccount;

    public function __construct($account = null)
    {
        parent::__construct($account);
        $this->currentAccount = $this->getAccount();
    }

    public function salesMailData()
    {

        return $this->indexData();
    }

    public function indexData()
    {
        # code...
        $account = $this->getAccount();
        $allSales        = $this->getAllSales();
        $soldItems       = $this->getItems();
        $solditemscount  = count($allSales);
        $salesTotalPrice = $allSales->sum('selling_price');
        $salesrevenue    = $this->getRevenue();
        $meansales       = $this->currentAccount->sales()->get()->Avg('selling_price');
        $meansales       = round($meansales, 2);
        $growth          = round($this->getProfitPercentage(), 2);

        $salesdata = [
            'soldItems'       => $soldItems,
            'allSales'        => $allSales,
            'solditemscount'  => $solditemscount,
            'salesTotalPrice' => $salesTotalPrice,
            'salesrevenue'    => $salesrevenue,
            'meansales'       => $meansales,
            'growth'          => $growth,
        ];

        return $salesdata;
    }

    public function getItemWithSales()
    {

    }

    public function createData()
    {
        # code...
        $stockdata = [
            "allStock" => $this->account->sales()->get(),
        ];

        return $stockdata;
    }

    public function showData($id)
    {
        # code...
        $allSales = $this->getSaleItem($id);

        return $allSales;
    }

    public function destroy($id)
    {
        //
        $result = Sale::destroy($id);
        if (! $result) {
            return false;
        }

        return $result;
    }

    //store sales item
    public function saveSalesItem($request)
    {
        $this->account->sales()->create(
            $request,
        );
    }

    public function saveSalesItemFromStock($request)
    {
        // dd($request);

        $result = $this->account->sales()->create(
            [
                'code'                => $request->code,
                'selling_price'       => $request->selling_price,
                'employee_id'         => Auth::id(),
                'retail_item_id'      => $request->retail_item_id,
                'sale_transaction_id' => $request->sale_transaction_id,
                'user_id'             => Auth::id(),
            ]

        );

        if (! $result) {
            return false;
        }

        return true;
    }
    public function getDisctictSoldItems()
    {
        $sales = $this->account->sales()->distinct('item_name', 'item_size')->get();
        foreach ($sales as $sale) {
            $sale->itemAmount = $this->account->sales()->where('item_name', $sale->item_name)->sum('itemAmount');
            $sale->price      = $this->account->sales()->where('item_name', $sale->item_name)->sum('selling_price');
        }

        return $sales;
    }

    //get sold items
    public function getItems($month = null, $year = null)
    {
        $sales = null;

        if (! $year) {
            $year = date('Y');
        }

        if ($month) {
            $items = $this->account->items()
                ->whereHas('sales')
                ->with('sales')
                ->whereMonth('created_at', '=', $month)
                ->whereYear('created_at', '=', $year)
                ->get();
        } else {
            // $month = date('m');
            $items = $this->account->items()
                ->whereHas('sales')
                ->with('sales')
                ->orderBy('created_at', "DESC")
                ->get();
        }
        // foreach ($items as $item) {
        //     $item['sales'] = $item->sales()->whereIn('retailsaleable_id', $this->account)->get();
        // }

        return $items;
    }

    //get all sales
    public function getAllSales($month = null, $year = null)
    {
        $sales = null;

        if (! $year) {
            $year = date('Y');
        }

        if ($month) {
            $sales = $this->account->sales()
                ->whereMonth('created_at', '=', $month)
                ->whereYear('created_at', '=', $year)
                ->with('items')
                ->get();
        } else {
            // $month = date('m');
            $sales = $this->account->sales()
                ->with('items')
                ->orderBy('created_at', "DESC")
                ->get();
        }

        foreach ($sales as $sale) {
            $sale['item'] = $sale->items()->first();
        }

        return $sales;
    }

    //getSoldItems
    public function getSoldItems($month = null, $year = null)
    {
        $sales = $this->getAllSales($month, $year);

        return count($sales);
    }

    //get sale by item id
    public function getSuppliesById($itemid)
    {
        $sale = $this->account->supplies()->where('id', $itemid)->get();

        return $sale;
    }

    //get supplies sales
    public function getSupplierSupplies($id)
    {
        $sale = $this->account->supplies()->where('supplier_id', $id)->get();

        return $sale;
    }

    //get employee sales
    public function getSalesByDate($startDate, $endDate)
    {
        $sale = $this->account->sales()->whereBetween('created_at', [$startDate . " 00:00:00", $endDate . " 23:59:59"])->get();
        return $sale;
    }

    public function getRevenue($month = null, $year = null)
    {
        $salesRevenue = 0;
        $salesexpense = 0;
        $sales        = null;
        if ($month || $year) {
            $sales = $this->account->sales()
                ->whereMonth('created_at', '=', $month)
                ->whereYear('created_at', '=', $year)
                ->sum('selling_price');
        } else {
            $sales = $this->account->sales()
                ->sum('selling_price');
        }

        $stockRepo    = new StockRepository($this->account);
        $salesexpense = $stockRepo->getStockExpense();
        // dd($sales);

        $salesRevenue = $sales - $salesexpense;
        # code...
        return $salesRevenue;
    }

    public function getProfitPercentage($month = null, $year = null)
    {
        $salesrevenue = $this->getRevenue($month, $year);
        if (! $salesrevenue) {
            return 0;
        }

        $salesTotalPrice = $this->getAllSales()->sum('selling_price');

        if (! $salesTotalPrice) {
            return 0;
        }

        $percentageProfit = ($salesrevenue / $salesTotalPrice) * 100;

        return $percentageProfit;
    }

    public function getSaleItem($item_id)
    {
        $item = $this->account->items()->where('id', $item_id)
            ->with('sales.employees.user')->first();
        return $item;
    }
    //get sale by item id
    public function getStockById($code)
    {
        $stock = $this->account->stocks()
            ->where('code', $code)
            ->with('items')
            ->first();
        return $stock;
    }

    public function getItemById($code)
    {
        $stock = $this->account->items()
            ->where('code', $code)
            ->whereHas('stocks')
            ->first();
        return $stock;
    }

    public function getMonthlySales($month = null, $year = null)
    {

        if (! $year) {
            $year = date('Y');
        }

        $transactions = null;
        if ($month) {
            $transactions = $this->account->saleTransactions()
                ->whereMonth('created_at', '=', $month)
                ->whereYear('created_at', '=', $year)
                ->get();
        } else {
            $transactions = $this->account->saleTransactions()
                ->whereMonth('created_at', '=', date('m'))
                ->whereYear('created_at', '=', $year)
                ->get();
        }

        //dd($transactions);
        return $transactions;
    }

    public function getDailySales()
    {
        $account = $this->account;

        $today = Carbon::today();

        $productSales = $account->saleTransactions()
            ->select('transaction_id', DB::raw('SUM(transaction_amount) as total_amount'))
            ->whereMonth('created_at', $today)
            ->groupBy('transaction_id')
            ->with('sales.items')
            ->get();

        return $productSales;
    }

    public function getSalesGrowth($key = null, $value = null)
    {
        $month = date('m');

        $currentTransactions = $this->account->saleTransactions()
            ->whereMonth('created_at', '=', $month)
            ->sum('paid_amount');

        $previousTransactions = $this->account->saleTransactions()
            ->whereMonth('created_at', '=', $month - 1)
            ->sum('paid_amount');

        if (! $currentTransactions) {
            $currentTransactions = 1;
        }

        if (! $previousTransactions) {
            $previousTransactions = 1;
        }

        $growth = (($currentTransactions - $previousTransactions) / $currentTransactions) * 100;

        $growth = number_format($growth, 2);
        return $growth;
    }

    //remove item from stock once sold

    public function removeStockItem($id)
    {
        # code...
        $stockRepo = new StockRepository($this->account);
        $result    = $stockRepo->removeStockItem($id);
        if (! $result) {
            return false;
        }

        return true;
    }

    //add sold item from retailItems
    public function addSoldItemFromRetailItems($item, $transId)
    {
        # code...

        $retailItem = $this->account->items()->where('code', $item->code)->first();
        $stock      = $retailItem->stocks()->first();
        $salesItem  = $stock;

        $salesItem['sale_transaction_id'] = $transId;
        $saveSales                        = $this->saveSalesItemFromStock($salesItem);
        if (! $saveSales) {
            return false;
        }

        $stockItem = $this->account->stocks()->where('id', $stock->id)->first();
        if ($stockItem) {
            $result = $this->removeStockItem($stockItem->id);
        }

        return $result;
    }

    //add sold item from stock
    public function addSoldItemFromStock($stock, $transId)
    {
        # code...
        $salesItem                        = $stock;
        $salesItem['sale_transaction_id'] = $transId;
        $saveSales                        = $this->saveSalesItemFromStock($salesItem);
        if (! $saveSales) {
            return false;
        }

        $result = $this->removeStockItem($stock->id);
        return $result;
    }

    public function getTransactionItems($transaction)
    {
        # code...
        $sales = $transaction->sales()
            ->with('items')
            ->get();
        return $sales;
    }

    public function getPaidSoldItems()
    {
        # code...
        $transactions = $this->account->saleTransactions()
            ->where('pay_status', true)
            ->with('sales')
            ->with('items')
            ->get();
        // dd($transactions);
        return $transactions;
    }

    public function getCreditItems()
    {
        # code...
        $transactions = $this->account
            ->saleTransactions()
            ->has('credit')
            ->with('sales')
            ->with('credit')
            ->with('customers')
            ->get();
        return $transactions;
    }

    public function setCreditItems($transaction_id, $customer_id)
    {
        # code...
        $transaction = $this->account
            ->saleTransactions()
            ->where('transaction_id', $transaction_id)
            ->first();

        $transactionUpdate = $transaction
            ->update(
                [
                    'on_credit'    => true,
                    'customers_id' => $customer_id,
                ]
            );

        $salesItems = $this->account->sales()
            ->where('sale_transaction_id', $transaction->id)
            ->update(
                [
                    'on_credit' => true,
                ]
            );
        return $transactionUpdate;
    }

    //employees
    public function getEmployeeSales($employee_id)
    {
        $employee = $this->account->employees()
            ->where('id', $employee_id)
            ->with('user')
            ->with('sales.items')
            ->first();
        return $employee;
    }

    public function getTopPerformingItems()
    {
        $sales = $this->account->sales()->select('retail_item_id', DB::raw('count(id) as total_sales'))
            ->groupBy('retail_item_id')
            ->orderByDesc('total_sales')
            ->take(10)
            ->with([
                'items.stocks' => function ($query) {
                    $query->select('retail_item_id', DB::raw('COUNT(*) as stock_count'))
                        ->groupBy('retail_item_id');
                },
            ])
            ->get();

        return $sales;
    }

    function getActiveTransaction($transaction_id)
    {
        $transaction = $this->account->saleTransactions()
            ->where('is_active', true)
            ->where('transaction_id', $transaction_id)
            ->where('user_id', Auth::id())
            ->with('sales.items')
            ->first();
        return $transaction;
    }

    public function saveSaleTransaction($transaction_id, $item)
    {
        # code...
        $transaction_amount = $item->selling_price;

        if (($transaction_id == "null" || $transaction_id == null) && $this->getActiveTransaction($transaction_id)) {
            $transaction_id = Sale::generateSaleTransactionId();
        } else {
            $activeTransaction = $this->getActiveTransaction($transaction_id);

            if ($activeTransaction) {
                $transaction_id     = $activeTransaction->transaction_id;
                $transaction_amount = $activeTransaction->transaction_amount + $transaction_amount;
            }
        }
        $tansaction = [
            "transaction_id"     => $transaction_id,
            "transaction_amount" => $transaction_amount,
        ];

        $transacResult = $this->storeTransaction($tansaction, SaleTransaction::SALE_TRANSACTION_ACTIVE);
        if (! $transacResult) {
            return false;
        }

        $salesResult = $this->addSoldItemFromRetailItems($item, $transacResult->id);

        if (! $salesResult) {
            return false;
        }

        $revenueRepo  = new RevenueRepository($this->account);
        $storeRevenue = $revenueRepo->saveRevenue($transaction_amount);

        return $transaction_id;
    }

    public function storeTransaction($transaction_data, $status)
    {
        $saleTransactionsRequest = new StoreSaleTransactionRequest();

        $saleTransactionsRequest->merge($transaction_data);

        $saleTransactionsController = new SaleTransactionController();
        $response                   = $saleTransactionsController->store($saleTransactionsRequest, $status);
        return $response;
    }

    public function getReceiptTotals(Retail $retail, SaleTransaction $saleTransaction)
    {
        $totals['sub_total']      = $saleTransaction->transaction_amount;
        $totals['total_discount'] = $saleTransaction->discount ?? 0;
        $totals['VAT']            = $saleTransaction->VAT ?? 0;
        $totals['total']          = $saleTransaction->total ?? $saleTransaction->transaction_amount;
        $totals['paid']           = $saleTransaction->paid_amount;
        $totals['change']         = $saleTransaction->balance;
        return $totals;
    }

}

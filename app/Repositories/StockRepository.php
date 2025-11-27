<?php

namespace App\Repositories;

use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class StockRepository
{
    private $account;
    public function __construct($account)
    {
        $this->account = $account;
    }

    public function stockMailData()
    {
        $allStocks = $this->getStock();
        $stocksitems = count($allStocks);
        $stocksexpense = $this->getStockExpense();
        $stocksexpectedSales = $this->getStockValue();
        $stocksrevenue = $stocksexpectedSales - $stocksexpense;

        $stockdata = array(
            'stockitems' => $allStocks,
            'stocksitemscount' => $stocksitems,
            'stocksexpense' => $stocksexpense,
            'stocksexpectedSales' => $stocksexpectedSales,
            'stocksrevenue' => $stocksrevenue,
        );

        return $stockdata;
    }

    public function saveStock($request)
    {

        $item = $this->account->items()->updateOrCreate(
            [
                'name' => $request->name,
                'brand' => $request->brand,
                'size' => $request->size,
            ],
            [

                'selling_price' => $request->selling_price,
                'image' => $request->stockImage,
                'buying_price' => $request->buying_price,
            ]
        );

        if (!$item) {
            return false;
        }

        return $item;
    }

    public function getDisctictStock()
    {
        $stocks = $this->account->stocks()->get();

        return $stocks;
    }

    public function getDisctictStockItems($month = null, $year = null)
    {
        $stocks = null;
        if (!$year) {
            $year = date('Y');
        }

        if ($month) {
            $stocks = $this->account->stocks()
                ->whereMonth('created_at', '=', $month)
                ->whereYear('created_at', '=', $year)
                ->get();
        } else {
            $stocks = $this->account->expenses()
                ->whereYear('created_at', '=', $year)
                ->get();
        }

        //dd($expenses->expense);

        return $stocks;
    }

    public function getAllStock($key = null, $value = null)
    {
        $stocks = null;
        if ($key && $value) {
            $stocks = $this->account->stocks()
                ->where($key, $value)
                ->with('items')
                ->get();
        } else {
            $stocks = $this->account->stocks()
                ->with('items')
                ->get();
        }

        return $stocks;
    }

    public function getAllItems($key = null, $value = null)
    {
        $stocks = null;
        if ($key && $value) {
            $stocks = $this->account->items()
                ->where($key, $value)
                ->whereHas('stocks')
                ->with('stocks')
                ->get();
        } else {
            $stocks = $this->account->items()
                ->whereHas('stocks')
                ->with('stocks')
                ->get();
        }

        return $stocks;
    }

    //get items
    public function getStock()
    {
        # code...
        $stockItems = $this->account->items()
            ->with('stocks')
            ->with(['sales' => function ($query) {
                $query->select('retail_item_id', DB::raw('COUNT(*) as sales_count'))
                    ->groupBy('retail_item_id');
            }])
            ->with(
                ['requiredItems' => function ($query) {
                    $query->select('retail_item_id', DB::raw('COUNT(*) as required_items_count'))
                        ->groupBy('retail_item_id');
                }]
            )
            ->get();
        // foreach ($stockItems as $stockItem) {
        //     $stockItem['item'] = $stockItem->stocks()->get();
        // }
        // dd($stockItems);
        return $stockItems;
    }

    //get stock items
    public function getStockItems($items_id)
    {
        $item = $this->account->items()
            ->with('stocks')
            ->orderBy('created_at', "DESC")
            ->where('id', $items_id)
            ->first();
        return $item;
    }

    public function getRetailItem($items_id)
    {
        $item = $this->account->items()
            ->where('id', $items_id)
            ->first();

        return $item;
    }

    //get stock value
    public function getStockValue()
    {
        # code...
        $stockValue = 0;
        $stocks = $this->account->stocks()->get();
        $stocks->sum('selling_price');
        return $stockValue;
    }

    //get stock value
    public function getStockExpense()
    {
        # code...
        $stockExpense = 0;
        $stocks = $this->account->stocks()->get();
        $stockExpense = $stocks->sum('buying_price');
        return $stockExpense;
    }

    //get sale by item id
    public function getStocksById($id)
    {
        $stock = $this->account->stocks()
            ->where('id', $id)
            ->with('items')
            ->first();
        if (!$stock) {
            return false;
        }

        $stock['item'] = $stock->items()->first();

        return $stock;
    }

    //get sale by item code
    public function getStockById($code)
    {
        $stock = $this->account->stocks()->where('code', $code)->first();
        $stock['item'] = $stock->items()->first();

        return $stock;
    }

    public function getRevenue()
    {

        $projectedsales = $this->account->items()->sum('selling_price');
        $stockexpense = $this->account->items()->sum('buying_price');
        $projectedRevenue = $projectedsales - $stockexpense;

        return $projectedRevenue;
    }

    public function getSaleItem($key, $value)
    {
        $sales = $this->account->sales()->where($key, $value)->orderBy('created_at', 'DESC')->get();
        //dd($sales);
        return $sales;
    }

    public function removeStockItem($id)
    {
        $result = Stock::destroy($id);
        if (!$result) {
            return false;
        }

        return true;
    }

    public function markRequired($id, $amount = null)
    {

        $item = $this->account->items()->where('id', $id)->first();

        if (!$item) {
            return false;
        }

        $stockUpdate = $item->update(
            [
                "is_required" => true,
            ]
        );
        $requiredRepo = new RequiredItemsRepository($this->account);

        $requiredResult = $requiredRepo->storeRequiredItems($item, $amount);
        $stockData["stockUpdate"] = $stockUpdate;

        if (!$requiredResult) {
            return false;
        }

        $stockData["requiredResult"] = $stockUpdate;
        return $stockData;
    }
}

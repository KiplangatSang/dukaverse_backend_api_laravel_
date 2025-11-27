<?php

namespace App\Repositories;

use App\Models\Retail;
use App\Models\RetailItem;
use App\Models\SaleTransaction;

class ItemRepository
{
    private $account;
    public function __construct($account)
    {
        $this->account = $account;
    }

    public function saveItems($request, $productColors, $productSizes, $fileUrlsToStore)
    {

        $item = $this->account->items()->create(
            [
                'name' => $request['name'],
                'brand' => $request['brand'],
                'size' => $request['size'],
                'selling_price' => $request['selling_price'],
                'image' => $request['image'] ?? $request['stockImageUrl'] ?? '',
                'buying_price' => $request['buying_price'],
                'code' => $request['code'],
                "required_when_below" => $request['required_when_below'] ?? 10,
                "product_colors" => json_encode($productColors),
                "product_sizes" => json_encode($productSizes),
                "product_images" => json_encode($fileUrlsToStore),
            ]
        );
        if (!$item) {
            return false;
        }

        $saveditem = $item
            ->where('id', $item->id)
            ->first();
        return $saveditem;
    }

    public function updateItems($request, $id)
    {

        // dd($request->all());

        $itemUpdate = $this->account->items()
            ->where('id', $id)
            ->update(
                $request,
            );

        if (!$itemUpdate) {
            return false;
        }

        return $itemUpdate;
    }

    public function getSalesItems(Retail $retail, SaleTransaction $saleTransaction)
    {
        $sold_items = $retail->items()
            ->whereHas('sales', function ($query) use ($saleTransaction) {
                $query->where('sale_transaction_id', $saleTransaction->id);
            })
            ->with(['sales' => function ($query) use ($saleTransaction) {
                $query->where('sale_transaction_id', $saleTransaction->id);
            }])
            ->get();
        $totalSellingPrice = $sold_items->sum(function ($item) {
            return $item->sales->sum('selling_price'); // Sum sellingprice from the sales relationship
        });

        $sold_items->each(function ($item) use ($totalSellingPrice) {
            $item->price = $item->sales->sum('selling_price'); // Adding the selling price sum as a new price column
        });
        return $sold_items;
    }

    public function getItems($month = null, $year = null)
    {
        $items = $this->account->items()
            ->orderBy('created_at', 'DESC')
            ->with('stocks')
            ->with('sales')
            ->with('requiredItems')
            ->get();

        return $items;
    }

    //get items
    public function getItem($id)
    {
        # code...
        $item = $this->account->items()
            ->where('id', $id)
            ->with('stocks')
            ->with('sales')
            ->with('requiredItems')
            ->first();
        return $item;
    }

    //get stock value
    public function getItemsValue()
    {
        # code...
        $value = 0;
        $items = $this->account->items()
            ->with('stock')
            ->get();

        $value = $items->selling_price * count($items->stocks);

        return $value;
    }

    //get stock value
    public function getItemsExpense()
    {
        # code...
        $value = 0;
        $items = $this->account->items()
            ->with('stocks')
            ->get();

        $value = $items->buying_price * count($items->stocks);
        return $value;
    }

    public function getRevenue()
    {
        $itemsSold = $this->account
            ->items()
            ->with('sales')
            ->get();
        $itemsInStore =
        $this->account
            ->items()
            ->with('stocks')
            ->get();

        $projectedsales = $itemsSold->selling_price * count($itemsSold->sales);
        $stockexpense = $itemsInStore->buying_price * count($itemsInStore->stocks);

        $projectedRevenue = $projectedsales - $stockexpense;

        return $projectedRevenue;
    }

    public function removeStockItem($id)
    {
        $result = RetailItem::destroy($id);
        if (!$result) {
            return false;
        }

        return true;
    }

    public function markRequired($id, $amount = null)
    {

        $item = $this->account->items()
            ->where('id', $id)
            ->first();

        if (!$item) {
            return false;
        }

        $itemUpdate = $item->update(
            [
                "is_required" => true,
            ]
        );
        $requiredRepo = new RequiredItemsRepository($this->account);

        $requiredResult = $requiredRepo->storeRequiredItems($item, $amount);

        $itemData["itemUpdate"] = $itemUpdate;
        if (!$requiredResult) {
            return false;
        }

        $itemData["requiredResult"] = $itemUpdate;
        return $itemData;
    }
}

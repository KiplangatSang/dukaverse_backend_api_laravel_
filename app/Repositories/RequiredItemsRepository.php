<?php
namespace App\Repositories;

use App\Models\RetailItem;

class RequiredItemsRepository
{
    private $account;
    public function __construct($account)
    {
        $this->account = $account;
    }

    public function indexData()
    {
        # code...
        $requireditems = $this->getAllRequiredItems();

        //dd($requiredItems);

        $requireditemscount = count($requireditems);
        $ordereditems       = count($requireditems->where("is_ordered", true));
        $requireditemscost  = $requireditems->sum('price');
        $pendingitems       = count($requireditems->where("is_ordered", false));
        $requiredItemsData  = [
            'requireditemscount' => $requireditemscount,
            'requireditems'      => $requireditems,
            'requireditemscost'  => $requireditemscost,
            'ordereditems'       => $ordereditems,
            'pendingitems'       => $pendingitems,
        ];

        return $requiredItemsData;
    }

    public function showData($id)
    {
        //
        $requireditems = $this->account->stocks()->where('stockName', $id)
            ->orderBy('created_at', 'DESC')
            ->get();
        $requiredItemsData = [
            'requireditems' => $requireditems,
        ];

        return $requiredItemsData;
    }

    public function getAllRequiredItems()
    {
        $requiredItems = $this->account->requiredItems()->with('items')->get();
        return $requiredItems;
    }

    //get sale by item id
    public function getRequiredItemsById($itemid)
    {
        $requiredItem = $this->account->requiredItems()->where('id', $itemid)->get();

        return $requiredItem;
    }

    public function updateRequiredItems($request)
    {
        # code...
        $result = $this->account->requiredItems()->update(
            $request,
        );

        if (! $result) {
            return false;
        }

        return $result;
    }

    //get employee sales
    public function getEmployeeRequiredItems($empid)
    {
        $requiredItem = $this->account->requiredItems()->where('employees_id', $empid)->get();
        return $requiredItem;
    }

    //get employee sales
    public function getRequiredItemsByDate($startDate, $endDate)
    {
        $requiredItem = $this->account->requiredItems()->whereBetween('created_at', [$startDate . " 00:00:00", $endDate . " 23:59:59"])->get();
        return $requiredItem;
    }

    public function getRequiredItemsCost()
    {
        $requiredItemPrice = $this->account->requiredItems()->sum('price');
        # code...
        return $requiredItemPrice;
    }

    public function getRequiredItems($key, $value)
    {
        $requiredItem = $this->account->requiredItems()->where($key, $value)->orderBy('created_at', 'DESC')->get();
        //dd($sales);
        return $requiredItem;
    }

    public function storeRequiredItems($item, $amount = null)
    {
        # code...
        if (! $amount) {
            $amount = 1;
        }

        $requiredResult = $this->account->requiredItems()->updateOrCreate([
            "retail_items_id" => $item->id,
        ], [
            "employees_id"    => Auth::id(),
            "required_amount" => $amount,
            "projected_cost"  => $item->selling_price,
        ]);

        if (! $requiredResult) {
            return false;
        }

        return true;
    }

    public function storeRequiredItem($item_id, $amount = null)
    {
        $item = RetailItem::where('id', $item_id)->first();
        # code...
        if (! $amount) {
            $amount = 1;
        }

        $requiredResult = $this->account->requiredItems()->updateOrCreate([
            "retail_items_id" => $item->id,
        ], [
            "employees_id"    => Auth::id(),
            "required_amount" => $amount,
            "projected_cost"  => $item->selling_price,
        ]);

        if (! $requiredResult) {
            return false;
        }

        return true;
    }
}

<?php
namespace App\Services;

use App\Repositories\OrdersRepository;
use Exception;
use Illuminate\Support\Str;
use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;

class OrderService extends BaseService
{
    protected OrdersRepository $ordersRepository;
    public function __construct(
        OrdersRepository $ordersRepository,
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
        $this->ordersRepository = $ordersRepository;
    }

    public function getIndexData()
    {
        return $this->ordersRepository->formatOrders();
    }

    public function getShowData($id)
    {
        return $this->ordersRepository->showOrderData($id);
    }

    public function createOrder($request)
    {
        $requiredItems = [];
        $projectedCost = 0;

        $items = $request->all();
        foreach ($items as $selected_item) {
            $requireditem = $this->account->requiredItems()
                ->where('id', $selected_item['id'])
                ->with('items')
                ->first();
            if (! $requireditem) {
                throw new Exception("You have not selected any item");
            }

            $item = [
                'id'     => $requireditem->items->id,
                'item'   => $requireditem->items->name,
                'brand'  => $requireditem->items->brand,
                'size'   => $requireditem->items->size,
                'amount' => $selected_item['required_amount'],
                'cost'   => ($requireditem->items->selling_price * $selected_item['required_amount']),
            ];

            $projectedCost += ($requireditem->items->selling_price * $selected_item['required_amount']);
            $requiredItems[$selected_item['id']] = $item;
        }
        if (empty($requiredItems)) {
            throw new Exception("You have not selected any item");
        }

        $miscellaneous = 0;
        $orderId       = "ORD" . Str::random(6);
        $actualCost    = $projectedCost + $miscellaneous;

        $order = $this->account->orders()->updateOrCreate(
            ["orderId" => $orderId],
            [
                "ordered_items"  => json_encode($requiredItems),
                "items_count"    => count($requiredItems),
                "pay_status"     => false,
                "order_status"   => -1,
                "projected_cost" => $projectedCost,
                "actual_cost"    => $actualCost,
            ]
        );

        foreach ($items as $selected_item) {
            $requireditem = $this->account->requiredItems()->where('id', $selected_item['id'])->first();
            $requireditem->update(
                [
                    'is_ordered'     => true,
                    'order_id'       => $order->id,
                    'ordered_amount' => $requireditem->ordered_amount + $selected_item['required_amount'],
                ]
            );
        }

        return $order;
    }
}

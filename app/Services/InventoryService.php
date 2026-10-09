<?php

namespace App\Services;

use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\Order;
use App\Models\RequiredItem;
use App\Models\Stock;
use App\Models\Supply;
use App\Models\RetailItem;
use App\Models\Supplier;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryService  extends BaseService
{

    public function __construct(
        StoreFileResource $storeFileResource,
       private ResponseHelper $responseHelper,
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }

    /**
     * Convert a required item to an order
     */
    public function convertRequiredItemToOrder($requiredItemId, $data)
    {

        try {
            $requiredItem = RequiredItem::findOrFail($requiredItemId);

            DB::beginTransaction();

            // Create order from required item
            $order = Order::create([
                'user_id' => $data['user_id'] ?? Auth::id(),
                'ownerable_type' => $requiredItem->ownerable_type,
                'ownerable_id' => $requiredItem->ownerable_id,
                'supplierable_type' => Supplier::class,
                'supplierable_id' => $data['supplier_id'],
                'status' => Order::ORDER_PLACED,
                'payment_status' => Order::UNPAID,
                'total_amount' => $requiredItem->projected_cost,
            ]);

            // Associate required item to order
            $requiredItem->update(['order_id' => $order->id]);

            // Attach retail item to order
            $order->items()->attach($requiredItem->retail_item_id, ['quantity' => $requiredItem->required_amount]);

            DB::commit();

            return ResponseHelper::respond($order->load('requiredItems', 'items'), 'Order created from required item successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to convert required item to order', 500, [$e->getMessage()]);
        }
    }

    /**
     * Convert an order to a supply
     */
    public function convertOrderToSupply($orderId, $data)
    {
        try {
            $order = Order::findOrFail($orderId);

            DB::beginTransaction();

            // Create supply from order
            $supply = Supply::create([
                'ownerable_type' => $order->ownerable_type,
                'ownerable_id' => $order->ownerable_id,
                'supplier_id' => $order->supplierable_id,
                'order_id' => $order->id, // For tracing
                'status' => 'pending', // Assuming status field
            ]);

            // Attach items from order to supply
            foreach ($order->items as $item) {
                $supply->supplyable()->attach($item->id, ['quantity' => $item->pivot->quantity]);
            }

            // Update order status
            $order->update(['status' => Order::PROCESSING]);

            DB::commit();

            return ResponseHelper::respond($supply->load('supplyable'), 'Supply created from order successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to convert order to supply', 500, [$e->getMessage()]);
        }
    }

    /**
     * Convert a supply to stock
     */
    public function convertSupplyToStock($supplyId, $data)
    {
        try {
            $supply = Supply::findOrFail($supplyId);

            DB::beginTransaction();

            $stocks = [];
            foreach ($supply->supplyable as $item) {
                $stock = Stock::create([
                    'ownerable_type' => $supply->ownerable_type,
                    'ownerable_id' => $supply->ownerable_id,
                    'stockable_type' => Supply::class,
                    'stockable_id' => $supply->id,
                    'retail_item_id' => $item->id,
                    'quantity' => $item->pivot->quantity,
                    'code' => Stock::generateStockId($item),
                ]);
                $stocks[] = $stock;
            }

            // Update supply status
            $supply->update(['status' => 'converted']);

            DB::commit();

            return ResponseHelper::respond($stocks, 'Stock created from supply successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to convert supply to stock', 500, [$e->getMessage()]);
        }
    }

    /**
     * Trace supply back to order
     */
    public function traceSupplyToOrder($supplyId)
    {
        try {
            $supply = Supply::with('order')->findOrFail($supplyId);

            if (!$supply->order) {
                return ResponseHelper::error('No order found for this supply', 404);
            }

            return ResponseHelper::respond($supply->order, 'Order traced successfully');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to trace supply to order', 500, [$e->getMessage()]);
        }
    }

    /**
     * Restock stock from supply
     */
    public function restockStockFromSupply($stockId, $supplyId, $quantity)
    {
        try {
            $stock = Stock::findOrFail($stockId);
            $supply = Supply::findOrFail($supplyId);

            // Check if supply has the item
            $supplyItem = $supply->supplyable()->where('retail_item_id', $stock->retail_item_id)->first();
            if (!$supplyItem || $supplyItem->pivot->quantity < $quantity) {
                return ResponseHelper::error('Insufficient quantity in supply', 400);
            }

            DB::beginTransaction();

            // Increase stock quantity
            $stock->increment('quantity', $quantity);

            // Decrease supply quantity
            $supply->supplyable()->updateExistingPivot($stock->retail_item_id, ['quantity' => $supplyItem->pivot->quantity - $quantity]);

            DB::commit();

            return ResponseHelper::respond($stock, 'Stock restocked successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to restock stock from supply', 500, [$e->getMessage()]);
        }
    }

    // Additional CRUD methods if needed
    public function index()
    {
        try {
            $orders = Order::with('requiredItems', 'supplyItems', 'items')->get();
            return ResponseHelper::respond($orders, 'Inventory data retrieved successfully');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to retrieve inventory data', 500, [$e->getMessage()]);
        }
    }
}

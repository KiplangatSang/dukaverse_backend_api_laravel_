<?php

namespace App\Http\Controllers\Retailer;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;
use Illuminate\Http\Request;
use App\Models\RequiredItem;
use App\Models\Retail;
use App\Models\RetailItem;
use App\Services\RequiredItemService;
use App\Services\StockService;
use Exception;

class MarketController extends BaseController
{

    public function __construct(
        protected ApiResource $apiResource,
        private RequiredItemService $requiredItemService,
        protected readonly StockService $stockService,
        protected readonly  RequiredItemService $requiredService,
    ) {
        parent::__construct($apiResource);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type = null)
    {
        try {
            $marketdata = [];
            if ($type) {
                $marketdata["items"] = RetailItem::where('itemable_type', Retail::class)
                    ->with('stocks')
                    ->with('supplyItems')
                    ->get();
            } else {
                $marketdata["items"] = RetailItem::where('itemable_type', Retail::class)
                    ->where('name', "like", "%" . $type . "%")
                    ->where('brand', "like", "%" . $type . "%")
                    ->with('stocks')
                    ->with('supplyItems')
                    ->get();
            }

            $requiredItems = $this->requiredItemService->requiredItems();

            $marketdata['requiredItems'] = $requiredItems;

            return $this->sendResponse(['marketdata' => $marketdata, "requiredItems" => $requiredItems], "Data fetched successfully");
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            //

            $items = $request->except('_token');
            foreach ($items as $key => $amount) {
                $this->stockService->markRequired($key, $amount);
                $this->requiredService->storeRequiredItem($key, $amount);
            }
            return redirect("/market");
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $marketdata["items"] = RetailItem::where('itemable_type', "App\Retails\Retail")
                ->with('stocks')
                ->with('supplyItems')
                ->get();

            $retailItem = RetailItem::where('id', $id)->first();
            $marketdata["retailItem"] = $retailItem;
            return view('client.market.index', compact('marketdata'));
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }
    public function destroy($id)
    {
        try {
            //

            $result = RequiredItem::destroy($id);

            if ($result) {
                return back()->with('success', 'Item removed successfully');
            } else {
                return back()->with('error', 'Could not remove this item');
            }
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }
}

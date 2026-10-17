<?php
namespace App\Http\Controllers;
use App\Http\Resources\ApiResource;

use App\Http\Requests\StoreStockRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Models\Stock;
use App\Notifications\StockUpdateNotification;
use App\Services\ExpenseService;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;

use function App\Notifications\toArray;

class StockController extends BaseController
{
     public function __construct(
        protected ApiResource $apiResource,
        private readonly StockService $stockService,
        private readonly ExpenseService $expenseService
    ) {
        parent::__construct($apiResource);
    }

    /**
     * Display a listing of the resource.
     */
    public function index($id = null)
    {
        //
        $stockdata = null;
        if ($id) {
            $stockdata = $this->getAccount()->items()->where('id', $id)
                ->with('stocks')->first();
                $stockdata = $stockdata->toArray();
        } else {
            $allStocks           = $this->stockService->getStock();
            $stocksitems         = count($allStocks);
            $stocksexpense       = $this->stockService->getStockExpense();
            $stocksexpectedSales = $this->stockService->getStockValue();
            $stocksrevenue       = $stocksexpectedSales - $stocksexpense;

            $stockdata = [
                'stockitems'          => $allStocks,
                'stocksitemscount'    => $stocksitems,
                'stocksexpense'       => $stocksexpense,
                'stocksexpectedSales' => $stocksexpectedSales,
                'stocksrevenue'       => $stocksrevenue,
            ];
        }

        if (! $stockdata) {
            return $this->sendError("Could not get Item");
        }

        return $this->sendResponse($stockdata, "success");
    }

    public function generatePDF(Request $request)
    {
        $user   = $this->user();
        $result = $user->notify(new StockUpdateNotification($this->retail()));
        return $this->sendResponse(["result" => $result], "success");

    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

        $this->stockService;
        $stockdata = $this->stockService->getStock();
        return view('client.stock.store.create', compact('stockdata'));
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($retail_item_id, StoreStockRequest $request)
    {
        //
        $retail_item = $this->stockService->getRetailItem($retail_item_id);
        $stockItem = null;
        for ($i = 0; $i < $request->quantity; $i++) {
            $stockItem = $this->getAccount()->stocks()->create(
                [
                    'code'           => $request->generate_code ? Stock::generateStockId($retail_item) : $retail_item->code,
                    'retail_item_id' => $retail_item->id,
                    'selling_price'  => $retail_item->selling_price,
                    'buying_price'   => $retail_item->buying_price,
                ]

            );
        }

        if (! $stockItem) {
            return $this->sendError("Could not add code",['message' => 'Could not add code'], 404);
        }

        $expense = $stockItem->buying_price;
        //save  expense
        $expenseResult = $this->expenseService->saveExpense($expense);
        if (! $expenseResult) {
            return $this->sendError(['message' => 'Sorry Could not save this item expense'], 500);
        }
        $item = $this->stockService->getRetailItem($retail_item_id);

        return $this->sendResponse($item->toArray(), "Code and expense has been added to the item");
    }

    /**
     * Display the specified resource.
     */
    public function show(Stock $stock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($stock)
    {
        //

        $allStocks  = $this->stockService->getStocksById($stock);
        $stocksdata = [
            'allStocks' => $allStocks,
        ];

        // dd($stocksdata['allStocks']->id);
        return view('client.stock.store.items.edit', compact('stocksdata'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStockRequest $request, $item, $stock)
    {
        //
        $stock = Stock::where('id', $stock)->first();

        $result = $stock->update(
            $request->validated(),
        );

        if (! $result) {
            return $this->sendError('error', ' Code could not be updated');
        }

        $stock = Stock::where('id', $stock)->first();

        return $this->sendResponse($stock, ' Code updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($item, $stock)
    {
        try {
            $result = Stock::destroy($stock);

            if (! $result) {
                return $this->sendError('error', ' Code could not be updated');
            }

            return $this->sendResponse("success", ' Code deleted successfully');
        } catch (Exception $e) {
            return $this->sendError($e, ' Code could not be updated');
        }
    }
}

<?php
namespace App\Http\Controllers;

class TerminalController extends BaseController
{

    private $salesrepo;

    public function salesRepository()
    {
        # code...
        $salesreo = new SalesRepository($this->getAccount());
        return $salesreo;
    }
    public function getPrompItems($key)
    {
        # code...
        $hint      = [];
        $stockRepo = new StockRepository($this->getAccount());
        $q         = $key;

        // $a = $stockRepo->getAllStock();
        $a = $stockRepo->getAllItems();

        // lookup all hints from array if $q is different from ""
        if ($q !== "") {
            $q   = strtolower($q);
            $len = strlen($q);
            foreach ($a as $stockItem) {
                if (stristr($q, substr($stockItem->code, 0, $len)) || stristr($q, substr($stockItem->brand, 0, $len)) || stristr($q, substr($stockItem->name, 0, $len))) {
                    if ($hint === "") {
                        $hint = null;
                    } else {
                        array_push($hint, $stockItem);
                    }
                }
            }
        }
        return $this->sendResponse($hint, 'success');
    }

    public function getSaleItem(Request $request, $item_id = null)
    {
        # code...

        $stock = null;

        $salesRepo = $this->salesRepository();
        $stock     = $salesRepo->getStockById($item_id ?? $request->item_id);
        if (! $stock) {
            return $this->sendError("Error", "No such item in store");
        }

        $transaction_id = $request->transaction_id ?? Sales::generateSaleTransactionId();

        $transactionResult = $salesRepo->saveSaleTransaction($transaction_id, $stock);
        if (! $transactionResult) {
            return $this->sendError("Error", "Transaction error");
        }

        $result = $salesRepo->getActiveTransaction($transactionResult);
        return $this->sendResponse($result, "Item saved successfully");
    }
}

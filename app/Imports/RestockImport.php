<?php

namespace App\Imports;

// Temporarily comment out Excel imports to prevent missing interface errors
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;

use App\Models\RetailItem;
use App\Models\Stock;

class RestockImport
{
    private $account;

    public function __construct($account)
    {
        $this->account = $account;
    }

    public function model(array $row)
    {
        // Check if item exists by code
        $item = RetailItem::where('code', $row['code'])->first();

        if (!$item) {
            // Create new item
            $item = RetailItem::create([
                'name' => $row['name'],
                'code' => $row['code'],
                'brand' => $row['brand'] ?? null,
                'buying_price' => $row['buying_price'],
                'selling_price' => $row['selling_price'],
                'description' => $row['description'] ?? null,
                'ownerable_type' => get_class($this->account),
                'ownerable_id' => $this->account->id,
            ]);
        }

        // Add stocks
        $quantity = $row['quantity'] ?? 1;
        for ($i = 0; $i < $quantity; $i++) {
            Stock::create([
                'code' => Stock::generateStockId($item),
                'retail_item_id' => $item->id,
                'selling_price' => $item->selling_price,
                'buying_price' => $item->buying_price,
                'ownerable_type' => get_class($this->account),
                'ownerable_id' => $this->account->id,
            ]);
        }

        return $item;
    }
}

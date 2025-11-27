<?php

namespace App\Exports;

// Temporarily comment out the Excel import to avoid missing interface error
// use Maatwebsite\Excel\Concerns\FromArray;
// use Maatwebsite\Excel\Concerns\WithHeadings;

class RestockTemplate
{
    public function headings(): array
    {
        return [
            'name',
            'code',
            'brand',
            'buying_price',
            'selling_price',
            'description',
            'quantity',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Sample Item',
                'SAMPLE001',
                'Sample Brand',
                10.00,
                15.00,
                'Sample description',
                5,
            ],
        ];
    }
}

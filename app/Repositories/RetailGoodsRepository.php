<?php
namespace App\Repositories;

class RetailGoodsRepository
{

    public function retailGoods()
    {
        # code...
        $retailGoods = [
            ["name" => "Clothes",
                "value" => "Clothes"],
            ["name" => "Shoes",
                "value" => "Shoes"],
            ["name" => "Electronics",
                "value" => "Electronics ie phones,Computers"],
            ["name" => "Pharmaceuticals",
                "value" => "Pharmaceuticals ie Medicines"],
            ["name" => "Food",
                "value" => "Food and Drinks"],
            ["name" => "BeautyProducts",
                "value" => "Beauty Products and Makeups"],
            ["name" => "Flowers",
                "value" => "Flowers"]];

        return $retailGoods;
    }
}

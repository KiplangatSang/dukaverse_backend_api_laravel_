<?php
namespace App\Services;

use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;

class GoodsandServicesService extends BaseService
{

    public function __construct(
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }


    const GOODS = [
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

    const SERVICES = [
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

    const INDUSTRIES = [
        ["name" => "Clothing",
            "value" => "Clothing"],
        ["name" => "Hospitality",
            "value" => "Hospitality"],
        ["name" => "Electronics",
            "value" => "Electronics ie phones,Computers"],
        ["name" => "Construction",
            "value" => "Construction"],
        ["name" => "Medicine and Pharmacy",
            "value" => "medicine and Pharmacy"],
        ["name" => "Beauty and Cosmetics",
            "value" => "Beauty Cosmetics"],
        ["name" => "Technology and IT",
            "value" => "Technology and IT"]];

}

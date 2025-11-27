<?php
namespace App\Services;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;

class LocationService extends BaseService
{
    //

    public function __construct(
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }

    public function showProductsInAllLocations()
    {

    }

    public function locationsToShowProducts()
    {

    }

    public function blockLocations()
    {

    }
}

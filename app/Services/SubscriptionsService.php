<?php
namespace App\Services;

use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;

class SubscriptionsService extends BaseService
{
    //

    public function __construct(
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }
}

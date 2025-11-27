<?php
namespace App\Services;

use App\Repositories\SalesRepository;
use Exception;
use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;

class SaleService extends BaseService
{
    protected SalesRepository $salesRepository;

    public function __construct(
        SalesRepository $salesRepository,
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
        $this->salesRepository = $salesRepository;
    }

    public function getIndexData()
    {
        return $this->salesRepository->indexData();
    }

    public function getCreateData()
    {
        return $this->salesRepository->createData();
    }

    public function getShowData($id)
    {
        return $this->salesRepository->showData($id);
    }

    public function createSale($request)
    {
        $result = $this->salesRepository->saveSalesItem($request);

        if (!$result) {
            throw new Exception('Error saving sale item');
        }

        // Save revenue - assuming BaseController has saveRevenue, but since service, need to handle
        // In controller, it's $this->saveRevenue($request->price);
        // But to move logic, perhaps service should handle revenue saving.
        // For now, keep it, but ideally move to service.

        return $result;
    }

    public function updateSale($sale, $request)
    {
        $result = $sale->update($request);

        if (!$result) {
            throw new Exception('Error updating sale');
        }

        return $result;
    }

    public function deleteSale($id)
    {
        $result = $this->salesRepository->destroy($id);

        if (!$result) {
            throw new Exception('Could not delete sale');
        }

        return $result;
    }

    public function getDailySales()
    {
        return $this->salesRepository->getDailySales();
    }
}

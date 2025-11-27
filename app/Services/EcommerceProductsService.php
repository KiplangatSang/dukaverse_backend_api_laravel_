<?php
namespace App\Services;

use App\Http\Controllers\BaseController;
use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EcommerceProductsService extends BaseService
{

    public function __construct(
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }

    public function showAllRetailProducts(Request $request)
    {

        $user = $this->user();

        $validator = Validator::make(request()->all(), [
            'show_all_products' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()]);
        }

        $result = $user->ecommerceSettings()->update([
            'show_all_products' => $request->show_all_products,
        ]);

        if (! $result) {
            return $this->responseHelper->error('Request failed', ['error' => "Could not update the product settings"]);
        }
        return $this->responseHelper->respond(['result' => $result, 'message' => "Product settings have been set"], 'Request successful');

    }

    public function removeProductsFromEcommerceSite(Request $request)
    {

        $user = $this->user();

        $validator = Validator::make(request()->all(), [
            'remove_products_in_low_stock' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()]);
        }

        $result = $user->ecommerceSettings()->update([
            'remove_products_in_low_stock' => $request->remove_products_in_low_stock,
        ]);

        if (! $result) {
            return $this->responseHelper->error('Request failed', ['error' => "Could not update the product settings"]);
        }
        return $this->responseHelper->respond(['result' => $result, 'message' => "Product settings have been set"], 'Request successful');

    }

    public function removeProductsInLowStockFromEcommerceSite(Request $request)
    {

        $user = $this->user();

        $validator = Validator::make(request()->all(), [
            'remove_products_in_low_stock' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()]);
        }

        $result = $user->ecommerceSettings()->update([
            'remove_products_in_low_stock' => $request->remove_products_in_low_stock,
        ]);

        if (! $result) {
            return $this->responseHelper->error('Request failed', ['error' => "Could not update the product settings"]);
        }
        return $this->responseHelper->respond(['result' => $result, 'message' => "Product settings have been set"], 'Request successful');

    }

    public function addProductToEcommerceSite(Request $request)
    {

    }

    public function removeAllProductsFromEcommerceSite(Request $request)
    {

        $user = $this->user();

        $validator = Validator::make(request()->all(), [
            'remove_all_products' => ['required', 'boolean'],
            'show_all_products'   => ['required', 'boolean:false'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()]);
        }

        $result = $user->ecommerceSettings()->update([
            'remove_all_products' => $request->remove_all_products,
            'show_all_products'   => $request->show_all_products,
        ]);

        if (! $result) {
            return $this->responseHelper->error('Request failed', ['error' => "Could not update the product settings"]);
        }
        return $this->responseHelper->respond(['result' => $result, 'message' => "Product settings have been set"], 'Request successful');

    }

    public function removeProductFromEcommerceWhenStockIsLow()
    {

    }

    public function getEcommerceProducts(Request $request)
    {

        $products = $this->ecommerce($request)->items;
        if (! $products) {
            return $this->responseHelper->error('Request failed', ['error' => "Could fetch products"]);
        }
        return $this->responseHelper->respond(['products' => $products, 'message' => "Products fetched successfully"], 'Request successful');
    }

    public function getEcommerceProduct(Request $request, $product_id)
    {

        $product = $this->ecommerce($request)->items()->with('sales', 'stocks')
        // ->where('id', $product_id)
            ->first();
        if (! $product) {
            return $this->responseHelper->error('Request failed', ['error' => "Could fetch product"]);
        }
        return $this->responseHelper->respond(['product' => $product, 'message' => "Product fetched successfully"], 'Request successful');

    }

}

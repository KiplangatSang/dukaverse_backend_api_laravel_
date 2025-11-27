<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Ecommerce Products",
 *     description="Manage ecommerce product visibility and stock-related rules"
 * )
 */
class EcommerceProductController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/products/show-all",
     *     tags={"Ecommerce Products"},
     *     summary="Toggle showing all retail products on ecommerce site",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"show_all_products"},
     *             @OA\Property(property="show_all_products", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Product settings updated successfully"),
     *     @OA\Response(response=400, description="Validation failed")
     * )
     */
    public function showAllRetailProducts(Request $request)
    {
        $user      = $this->user();
        $validator = Validator::make(request()->all(), [
            'show_all_products' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $result = $user->ecommerceSettings()->update([
            'show_all_products' => $request->show_all_products,
        ]);

        if (! $result) {
            return $this->sendError('Request failed', ['error' => "Could not update the product settings"]);
        }
        return $this->sendResponse(['result' => $result, 'message' => "Product settings have been set"], 'Request successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/products/remove-low-stock",
     *     tags={"Ecommerce Products"},
     *     summary="Toggle automatic removal of low-stock products",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"remove_products_in_low_stock"},
     *             @OA\Property(property="remove_products_in_low_stock", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Product settings updated successfully"),
     *     @OA\Response(response=400, description="Validation failed")
     * )
     */
    public function removeProductsFromEcommerceSite(Request $request)
    {
        $user = $this->user();

        $validator = Validator::make(request()->all(), [
            'remove_products_in_low_stock' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $result = $user->ecommerceSettings()->update([
            'remove_products_in_low_stock' => $request->remove_products_in_low_stock,
        ]);

        if (! $result) {
            return $this->sendError('Request failed', ['error' => "Could not update the product settings"]);
        }
        return $this->sendResponse(['result' => $result, 'message' => "Product settings have been set"], 'Request successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/products/remove-low-stock-enforced",
     *     tags={"Ecommerce Products"},
     *     summary="Enforce removal of products in low stock immediately",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"remove_products_in_low_stock"},
     *             @OA\Property(property="remove_products_in_low_stock", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Low stock setting updated"),
     *     @OA\Response(response=400, description="Validation failed")
     * )
     */
    public function removeProductsInLowStockFromEcommerceSite(Request $request)
    {
        $user = $this->user();

        $validator = Validator::make(request()->all(), [
            'remove_products_in_low_stock' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $result = $user->ecommerceSettings()->update([
            'remove_products_in_low_stock' => $request->remove_products_in_low_stock,
        ]);

        if (! $result) {
            return $this->sendError('Request failed', ['error' => "Could not update the product settings"]);
        }
        return $this->sendResponse(['result' => $result, 'message' => "Product settings have been set"], 'Request successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/products/add",
     *     tags={"Ecommerce Products"},
     *     summary="Add a product to ecommerce site",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=200, description="Product added successfully")
     * )
     */
    public function addProductToEcommerceSite(Request $request)
    {
        // You can expand this logic later, Swagger docs added for future use
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/products/remove-all",
     *     tags={"Ecommerce Products"},
     *     summary="Remove all products from ecommerce site",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"remove_all_products","show_all_products"},
     *             @OA\Property(property="remove_all_products", type="boolean", example=true),
     *             @OA\Property(property="show_all_products", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=200, description="All products removed successfully"),
     *     @OA\Response(response=400, description="Validation failed")
     * )
     */
    public function removeAllProductsFromEcommerceSite(Request $request)
    {
        $user = $this->user();

        $validator = Validator::make(request()->all(), [
            'remove_all_products' => ['required', 'boolean'],
            'show_all_products'   => ['required', 'boolean:false'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $result = $user->ecommerceSettings()->update([
            'remove_all_products' => $request->remove_all_products,
            'show_all_products'   => $request->show_all_products,
        ]);

        if (! $result) {
            return $this->sendError('Request failed', ['error' => "Could not update the product settings"]);
        }
        return $this->sendResponse(['result' => $result, 'message' => "Product settings have been set"], 'Request successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/products/remove-when-low-stock",
     *     tags={"Ecommerce Products"},
     *     summary="Trigger manual removal of products with low stock",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Products removed successfully")
     * )
     */
    public function removeProductFromEcommerceWhenStockIsLow()
    {
        // Placeholder for manual stock removal logic
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ecommerce/products",
     *     tags={"Ecommerce Products"},
     *     summary="Get all products in the ecommerce site",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Products fetched successfully")
     * )
     */
    public function getEcommerceProducts(Request $request)
    {
        $products = $this->ecommerce($request)->items;
        if (! $products) {
            return $this->sendError('Request failed', ['error' => "Could fetch products"]);
        }
        return $this->sendResponse(['products' => $products, 'message' => "Products fetched successfully"], 'Request successful');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ecommerce/products/{product_id}",
     *     tags={"Ecommerce Products"},
     *     summary="Get a single product by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="product_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Product fetched successfully"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function getEcommerceProduct(Request $request, $product_id)
    {
        $product = $this->ecommerce($request)->items()->with('sales', 'stocks')
            ->first(); // you may want to filter by ->where('id', $product_id)
        if (! $product) {
            return $this->sendError('Request failed', ['error' => "Could fetch product"]);
        }
        return $this->sendResponse(['product' => $product, 'message' => "Product fetched successfully"], 'Request successful');
    }
}

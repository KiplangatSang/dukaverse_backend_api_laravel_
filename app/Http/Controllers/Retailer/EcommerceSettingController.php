<?php

namespace App\Http\Controllers\Retailer;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StoreEcommerceSettingRequest;
use App\Http\Requests\UpdateEcommerceSettingRequest;
use App\Services\EcommerceSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="EcommerceSetting",
 *     description="API Endpoints of Ecommerce Settings"
 * )
 */
class EcommerceSettingController extends BaseController
{
    protected EcommerceSettingService $service;

    public function __construct(EcommerceSettingService $service)
    {
        $this->service = $service;
    }

    // Temporarily disable Swagger annotation for store() to fix missing schema error
    /*
    @OA\Post(
        path="/v1/ecommerce/settings",
        tags={"EcommerceSetting"},
        summary="Create or update ecommerce settings",
        security={{"sanctum":{}}},
        @OA\RequestBody(
            required=true,
            @OA\JsonContent(ref="#/components/schemas/StoreEcommerceSettingRequest")
        ),
        @OA\Response(
            response=200,
            description="Settings saved successfully",
        ),
        @OA\Response(
            response=400,
            description="Validation error"
        ),
        @OA\Response(
            response=500,
            description="Internal server error"
        )
    )
    */
    public function store(StoreEcommerceSettingRequest $request): JsonResponse
    {
        try {
            return $this->service->saveEcommerceSettings($request);
        } catch (\Exception $e) {
            return $this->sendError('Failed to save settings', [$e->getMessage()], 500);
        }
    }

    // Other CRUD methods as needed, example update, show, index and destroy can be added here similarly

}

<?php

namespace App\Http\Controllers\Retailer;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StoreEcommerceVendorRequest;
use App\Http\Requests\UpdateEcommerceVendorRequest;
use App\Services\EcommerceVendorService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="EcommerceVendor",
 *     description="API Endpoints of Ecommerce Vendor"
 * )
 */
class EcommerceVendorController extends BaseController
{
    protected EcommerceVendorService $vendorService;

    public function __construct(EcommerceVendorService $vendorService)
    {
        $this->vendorService = $vendorService;
    }

    /**
    * @OA\Get(
    *     path="/v1/ecommerce/vendors",
    *     tags={"EcommerceVendor"},
    *     summary="List all ecommerce vendors accessible to the user",
    *     security={{"sanctum":{}}},
    *     @OA\Response(
    *         response=200,
    *         description="Successful retrieval of vendors",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(
    *                 type="object",
    *                 @OA\Property(property="id", type="integer", example=1),
    *                 @OA\Property(property="name", type="string", example="Vendor Name"),
    *                 @OA\Property(property="email", type="string", example="vendor@example.com"),
    *                 @OA\Property(property="phone", type="string", example="+1234567890"),
    *                 @OA\Property(property="created_at", type="string", format="date-time"),
    *                 @OA\Property(property="updated_at", type="string", format="date-time")
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Internal server error"
    *     )
    * )
    */
    public function index()
    {
        try {
            $vendors = $this->vendorService->listVendors();
            return $this->sendResponse($vendors, 'Vendors retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Failed to retrieve vendors', [$e->getMessage()], 500);
        }
    }

    /**
    * @OA\Post(
    *     path="/v1/ecommerce/vendors",
    *     tags={"EcommerceVendor"},
    *     summary="Create a new ecommerce vendor",
    *     security={{"sanctum":{}}},
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             type="object",
    *             required={"name", "email"},
    *             @OA\Property(property="name", type="string", example="New Vendor"),
    *             @OA\Property(property="email", type="string", example="newvendor@example.com"),
    *             @OA\Property(property="phone", type="string", example="+1234567890")
    *         )
    *     ),
    *     @OA\Response(
    *         response=201,
    *         description="Vendor created successfully",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="id", type="integer", example=1),
    *             @OA\Property(property="name", type="string", example="New Vendor"),
    *             @OA\Property(property="email", type="string", example="newvendor@example.com"),
    *             @OA\Property(property="phone", type="string", example="+1234567890"),
    *             @OA\Property(property="created_at", type="string", format="date-time"),
    *             @OA\Property(property="updated_at", type="string", format="date-time")
    *         )
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="Unauthorized to create ecommerce vendor"
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Internal server error"
    *     )
    * )
    */
    public function store(StoreEcommerceVendorRequest $request)
    {
        try {
            $vendor = $this->vendorService->createVendor($request->validated());
            if (!$vendor) {
                return response()->json(['error' => 'Unauthorized to create ecommerce vendor'], 403);
            }
            return $this->sendResponse($vendor, 'Vendor created successfully', 201);
        } catch (\Exception $e) {
            return $this->sendError('Failed to create vendor', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/v1/ecommerce/vendors/{vendor}",
     *     tags={"EcommerceVendor"},
     *     summary="Get details of a specific ecommerce vendor",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="vendor",
     *         in="path",
     *         required=true,
     *         description="Vendor ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor details retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Vendor Name"),
     *             @OA\Property(property="email", type="string", example="vendor@example.com"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $vendor = $this->vendorService->getVendor($id);
            return $this->sendResponse($vendor, 'Vendor retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Failed to retrieve vendor', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/v1/ecommerce/vendors/{vendor}",
     *     tags={"EcommerceVendor"},
     *     summary="Update an ecommerce vendor",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="vendor",
     *         in="path",
     *         required=true,
     *         description="Vendor ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "email"},
     *             @OA\Property(property="name", type="string", example="Updated Vendor"),
     *             @OA\Property(property="email", type="string", example="updated@example.com"),
     *             @OA\Property(property="phone", type="string", example="+1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Updated Vendor"),
     *             @OA\Property(property="email", type="string", example="updated@example.com"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function update(UpdateEcommerceVendorRequest $request, string $id)
    {
        try {
            $vendor = $this->vendorService->updateVendor($id, $request->validated());
            return $this->sendResponse($vendor, 'Vendor updated successfully');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update vendor', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/v1/ecommerce/vendors/{vendor}",
     *     tags={"EcommerceVendor"},
     *     summary="Delete an ecommerce vendor",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="vendor",
     *         in="path",
     *         required=true,
     *         description="Vendor ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
         try {
             $this->vendorService->deleteVendor($id);
             return $this->sendResponse(null, 'Vendor deleted successfully');
         } catch (\Exception $e) {
             return $this->sendError('Failed to delete vendor', [$e->getMessage()], 500);
         }
     }
}

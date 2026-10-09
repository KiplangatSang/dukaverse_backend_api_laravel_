<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;

use App\Models\Coupon;
use App\Services\DeliveryApp\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends BaseController
{
    public function __construct(
        ApiResource $apiResource,
        private readonly CouponService $couponService
    ) {
        parent::__construct($apiResource);
    }
    /**
     * @OA\Info(
     *     title="Dukaverse API",
     *     version="1.0.0",
     *     description="API documentation for managing Coupons"
     * )
     *
     * @OA\SecurityScheme(
     *     securityScheme="bearerAuth",
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT"
     * )
     */

    /**
     * @OA\Get(
     *     path="/api/v1/coupons",
     *     summary="Get all coupons",
     *     tags={"Coupons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Coupons fetched successfully")
     * )
     */
    public function index()
    {
        try {
            $result  = $this->couponService->index();
            if (isset($result['error'])) {
                return $this->sendError($result['error'], [], $result['httpCode'] ?? 500);
            }
            return $this->sendResponse($result['data'], 'Coupons fetched successfully');
        } catch (\Exception $e) {
            return $this->sendError('Server error', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/coupons/create",
     *     summary="Get coupon creation metadata",
     *     tags={"Coupons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Coupon creation metadata fetched successfully")
     * )
     */
    public function create()
    {
        try {
            $result  = $this->couponService->create();
            if (isset($result['error'])) {
                return $this->sendError($result['error'], [], $result['httpCode'] ?? 500);
            }
            return $this->sendResponse($result['data'], 'Coupon creation metadata fetched successfully');
        } catch (\Exception $e) {
            return $this->sendError('Server error', $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/coupons",
     *     summary="Create a new coupon",
     *     tags={"Coupons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code","name","discount_type","discount_value","is_active"},
     *             @OA\Property(property="code", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="discount_type", type="string", enum={"percentage","fixed"}),
     *             @OA\Property(property="discount_value", type="number", format="float"),
     *             @OA\Property(property="minimum_amount", type="number", format="float"),
     *             @OA\Property(property="maximum_discount", type="number", format="float"),
     *             @OA\Property(property="usage_limit", type="integer"),
     *             @OA\Property(property="starts_at", type="string", format="date-time"),
     *             @OA\Property(property="expires_at", type="string", format="date-time"),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="applicable_tiers", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Coupon created successfully"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        try {

            $result  = $this->couponService->store($request->all());
            if (isset($result['error'])) {
                return $this->sendError($result['error'], [], $result['httpCode'] ?? 500);
            }
            return $this->sendResponse($result['data'], 'Coupon created successfully');
        } catch (\Exception $e) {
            return $this->sendError('Server error', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/coupons/{id}",
     *     summary="Get a coupon by ID",
     *     tags={"Coupons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Coupon fetched successfully"),
     *     @OA\Response(response=404, description="Coupon not found")
     * )
     */
    public function show(string $id)
    {
        try {
            $result  = $this->couponService->show($id);
            if (isset($result['error'])) {
                return $this->sendError($result['error'], [], $result['httpCode']
                    ?? 500);
            }
            return $this->sendResponse($result['data'], 'Coupon fetched successfully');
        } catch (\Exception $e) {
            return $this->sendError('Server error', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/coupons/{id}/edit",
     *     summary="Get a coupon for editing",
     *     tags={"Coupons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Coupon to edit fetched successfully")
     * )
     */
    public function edit(string $id)
    {
        try {
            $result  = $this->couponService->edit($id);
            if (isset($result['error'])) {
                return $this->sendError($result['error'], [], $result['httpCode']

                    ?? 500);
            }
            return $this->sendResponse($result['data'], 'Coupon to edit fetched successfully');
        } catch (\Exception $e) {
            return $this->sendError('Server error', $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/coupons/{id}",
     *     summary="Update a coupon",
     *     tags={"Coupons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code","name","discount_type","discount_value","is_active"},
     *             @OA\Property(property="code", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="discount_type", type="string", enum={"percentage","fixed"}),
     *             @OA\Property(property="discount_value", type="number", format="float"),
     *             @OA\Property(property="minimum_amount", type="number", format="float"),
     *             @OA\Property(property="maximum_discount", type="number", format="float"),
     *             @OA\Property(property="usage_limit", type="integer"),
     *             @OA\Property(property="starts_at", type="string", format="date-time"),
     *             @OA\Property(property="expires_at", type="string", format="date-time"),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="applicable_tiers", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Coupon updated successfully"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function update(Request $request, string $id)
    {
        try {

            $result  = $this->couponService->update($request->all(), $id);
            if (isset($result['error'])) {
                return $this->sendError($result['error'], [], $result['httpCode']
                    ?? 500);
            }
            return $this->sendResponse($result['data'], 'Coupon updated successfully');
        } catch (\Exception $e) {
            return $this->sendError('Server error', $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/coupons/{id}",
     *     summary="Delete a coupon",
     *     tags={"Coupons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Coupon deleted successfully"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function destroy(string $id)
    {
        try {
            $result  = $this->couponService->destroy($id);
            if (isset($result['error'])) {
                return $this->sendError($result['error'], [], $result['httpCode']

                    ?? 500);
            }
            return $this->sendResponse($result['data'], 'Coupon deleted successfully');
        } catch (\Exception $e) {
            return $this->sendError('Server error', $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/coupons/validate",
     *     summary="Validate a coupon code",
     *     tags={"Coupons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code","tier_id","amount"},
     *             @OA\Property(property="code", type="string"),
     *             @OA\Property(property="tier_id", type="integer"),
     *             @OA\Property(property="amount", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Coupon validation result"),
     *     @OA\Response(response=400, description="Invalid coupon")
     * )
     */
    public function validateCoupon(Request $request)
    {
        try {

            $result  = $this->couponService->validateCoupon($request->all());
            if (isset($result['error'])) {
                return $this->sendError($result['error'], [], $result['httpCode']
                    ?? 500);
            }
            return $this->sendResponse($result['data'], 'Coupon validated successfully');
        } catch (\Exception $e) {
            return $this->sendError('Server error', $e->getMessage());
        }
    }
}

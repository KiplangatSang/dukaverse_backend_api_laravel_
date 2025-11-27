<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends BaseController
{
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
        $coupons = Coupon::with('ownerable')->get();
        return $this->sendResponse(['coupons' => $coupons], 'Coupons fetched successfully');
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
        $discount_types = ['percentage', 'fixed'];

        return $this->sendResponse([
            'discount_types' => $discount_types,
        ], 'Coupon creation data fetched successfully');
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
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'unique:coupons,code'],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['required', 'boolean'],
            'applicable_tiers' => ['nullable', 'array'],
            'applicable_tiers.*' => ['integer', 'exists:tiers,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $dukaverse = $this->dukaverse();
        $coupon = $dukaverse->coupons()->create($request->all());

        if (!$coupon) {
            return $this->sendError('Bad request', [
                'errors' => $validator->errors(),
                'message' => 'Coupon could not be created',
            ]);
        }

        return $this->sendResponse(['coupon' => $coupon], 'Coupon created successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/coupons/{id}",
     *     summary="Get a coupon by ID",
     *     tags={"Coupons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", req/v1uired=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Coupon fetched successfully"),
     *     @OA\Response(response=404, description="Coupon not found")
     * )
     */
    public function show(string $id)
    {
        $coupon = Coupon::with(['ownerable', 'subscriptions'])->find($id);

        if (!$coupon) {
            return $this->sendError('Coupon not found', [], 404);
        }

        return $this->sendResponse(['coupon' => $coupon], 'Coupon fetched successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/coupons/{id}/edit",
     *     summary="Get a coupon for editing",
     *     tags={"Coupons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", req/v1uired=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Coupon to edit fetched successfully")
     * )
     */
    public function edit(string $id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return $this->sendError('Coupon not found', [], 404);
        }

        $discount_types = ['percentage', 'fixed'];

        return $this->sendResponse([
            'coupon' => $coupon,
            'discount_types' => $discount_types,
        ], 'Coupon to edit fetched successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/coupons/{id}",
     *     summary="Update a coupon",
     *     tags={"Coupons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", req/v1uired=true, @OA\Schema(type="string")),
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
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return $this->sendError('Coupon not found', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'unique:coupons,code,' . $id],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['required', 'boolean'],
            'applicable_tiers' => ['nullable', 'array'],
            'applicable_tiers.*' => ['integer', 'exists:tiers,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $result = $coupon->update($request->all());

        if (!$result) {
            return $this->sendError('Bad request', [
                'errors' => $validator->errors(),
                'message' => 'Coupon could not be updated',
            ]);
        }

        $coupon = Coupon::find($id);
        return $this->sendResponse(['coupon' => $coupon], 'Coupon updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/coupons/{id}",
     *     summary="Delete a coupon",
     *     tags={"Coupons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", req/v1uired=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Coupon deleted successfully"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function destroy(string $id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return $this->sendError('Coupon not found', [], 404);
        }

        // Check if coupon is being used by active subscriptions
        if ($coupon->subscriptions()->where('is_active', true)->exists()) {
            return $this->sendError('Cannot delete coupon that is being used by active subscriptions', [], 400);
        }

        $result = $coupon->delete();

        if (!$result) {
            return $this->sendError('Bad request', ['message' => 'Coupon could not be deleted']);
        }

        return $this->sendResponse([], 'Coupon deleted successfully');
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
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string'],
            'tier_id' => ['required', 'integer', 'exists:tiers,id'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return $this->sendError('Invalid coupon code', [], 400);
        }

        if (!$coupon->isValid()) {
            return $this->sendError('Coupon is not valid or has expired', [], 400);
        }

        if (!$coupon->isApplicableToTier($request->tier_id)) {
            return $this->sendError('Coupon is not applicable to this tier', [], 400);
        }

        if (!$coupon->meetsMinimumAmount($request->amount)) {
            return $this->sendError('Minimum purchase amount not met', [
                'minimum_amount' => $coupon->minimum_amount
            ], 400);
        }

        $discount = $coupon->calculateDiscount($request->amount);
        $finalAmount = max(0, $request->amount - $discount);

        return $this->sendResponse([
            'coupon' => $coupon,
            'discount' => $discount,
            'final_amount' => $finalAmount,
            'is_valid' => true,
        ], 'Coupon validated successfully');
    }
}

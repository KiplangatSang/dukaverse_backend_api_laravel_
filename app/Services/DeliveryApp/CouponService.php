<?php

namespace App\Services\DeliveryApp;

use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\Coupon;
use App\Models\User;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CouponService extends BaseService
{

    public function __construct(
        protected readonly StoreFileResource $storeFileResource,
        protected readonly ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }


    public function index()
    {
        $coupons = Coupon::with('ownerable')->get();
        return $this->responseHelper->respond(['coupons' => $coupons], 'Coupons fetched successfully');
    }


    public function create()
    {
        $discount_types = ['percentage', 'fixed'];

        return $this->responseHelper->respond([
            'discount_types' => $discount_types,
        ], 'Coupon creation data fetched successfully');
    }


    public function store(array $data)
    {
        $validator = Validator::make($data, [
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
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()]);
        }

        $coupon = $this->account->coupons()->create($data);

        if (!$coupon) {
            return $this->responseHelper->error('Bad request', [
                'errors' => $validator->errors(),
                'message' => 'Coupon could not be created',
            ]);
        }

        return $this->responseHelper->respond(['coupon' => $coupon], 'Coupon created successfully');
    }

    public function show(string $id)
    {
        $coupon = Coupon::with(['ownerable', 'subscriptions'])->find($id);

        if (!$coupon) {
            return $this->responseHelper->error('Coupon not found', [], 404);
        }

        return $this->responseHelper->respond(['coupon' => $coupon], 'Coupon fetched successfully');
    }



    public function edit(string $id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return $this->responseHelper->error('Coupon not found', [], 404);
        }

        $discount_types = ['percentage', 'fixed'];

        return $this->responseHelper->respond([
            'coupon' => $coupon,
            'discount_types' => $discount_types,
        ], 'Coupon to edit fetched successfully');
    }


    public function update($data, string $id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return $this->responseHelper->error('Coupon not found', [], 404);
        }

        $validator = Validator::make($data, [
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
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()]);
        }

        $result = $coupon->update($data);

        if (!$result) {
            return $this->responseHelper->error('Bad request', [
                'errors' => $validator->errors(),
                'message' => 'Coupon could not be updated',
            ]);
        }

        $coupon = Coupon::find($id);
        return $this->responseHelper->respond(['coupon' => $coupon], 'Coupon updated successfully');
    }


    public function destroy(string $id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return $this->responseHelper->error('Coupon not found', [], 404);
        }

        // Check if coupon is being used by active subscriptions
        if ($coupon->subscriptions()->where('is_active', true)->exists()) {
            return $this->responseHelper->error('Cannot delete coupon that is being used by active subscriptions', [], 400);
        }

        $result = $coupon->delete();

        if (!$result) {
            return $this->responseHelper->error('Bad request', ['message' => 'Coupon could not be deleted']);
        }

        return $this->responseHelper->respond([], 'Coupon deleted successfully');
    }


    public function validateCoupon($data)
    {
        $validator = Validator::make($data, [
            'code' => ['required', 'string'],
            'tier_id' => ['required', 'integer', 'exists:tiers,id'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()]);
        }

        $coupon = Coupon::where('code', $data['code'])->first();

        if (!$coupon) {
            return $this->responseHelper->error('Invalid coupon code', [], 400);
        }

        if (!$coupon->isValid()) {
            return $this->responseHelper->error('Coupon is not valid or has expired', [], 400);
        }

        if (!$coupon->isApplicableToTier($data['tier_id'])) {
            return $this->responseHelper->error('Coupon is not applicable to this tier', [], 400);
        }

        if (!$coupon->meetsMinimumAmount($data['amount'])) {
            return $this->responseHelper->error('Minimum purchase amount not met', [
                'minimum_amount' => $coupon->minimum_amount
            ], 400);
        }

        $discount = $coupon->calculateDiscount($data['amount']);
        $finalAmount = max(0, $data['amount'] - $discount);

        return $this->responseHelper->respond([
            'coupon' => $coupon,
            'discount' => $discount,
            'final_amount' => $finalAmount,
            'is_valid' => true,
        ], 'Coupon validated successfully');
    }
}

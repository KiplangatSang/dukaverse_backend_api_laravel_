<?php

namespace App\Services;

use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\Tier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TierService  extends BaseService
{

    public function __construct(
        StoreFileResource $storeFileResource,
        private ResponseHelper $responseHelper,
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }


    public function getAllTiers()
    {
        try {
            $tiers = Tier::all();
            return $this->responseHelper->respond(['tiers' => $tiers->toArray()], 'Tiers fetched successfully');
        } catch (\Exception $e) {
            return $this->responseHelper->error('Error fetching tiers', ['error' => $e->getMessage()]);
        }
    }


    public function create()
    {
        $tier_types        = Tier::tier_types;
        $billing_durations = Tier::BILLINGDURATIONS;

        return $this->responseHelper->respond([
            'tier_types'        => $tier_types,
            'billing_durations' => $billing_durations,
        ], 'Tiers data fetched successfully');
    }


    public function store($data)
    {
        $validator = Validator::make($data, [
            "name"             => ['required'],
            "description"      => ['required'],
            "type"             => ['required'],
            "price"            => ['required'],
            "benefits"         => ['required'],
            "billing_duration" => ['required'],
            "is_active"        => ['required'],
            "is_recommended"   => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()]);
        }

        $tier      = $this->account->tiers()->create($data);

        if (! $tier) {
            return $this->responseHelper->error('Bad request', [
                'errors'  => $validator->errors(),
                'message' => 'Tier could not be created',
            ]);
        }

        return $this->responseHelper->respond(['tier' => $tier], 'Tier created successfully');
    }


    public function show(string $id)
    {
        $tier = Tier::where('id', $id)->first();

        if (!$tier) {
            return $this->responseHelper->error('Tier not found', ['message' => 'No tier found with the given ID']);
        }
        return $this->responseHelper->respond(['tier' => $tier], 'Tier fetched successfully');
    }


    public function edit(string $id)
    {
        $tier              = Tier::where('id', $id)->first();
        if (!$tier) {
            return $this->responseHelper->error('Tier not found', ['message' => 'No tier found with the given ID']);
        }
        $tier_types        = Tier::tier_types;
        $billing_durations = Tier::BILLINGDURATIONS;

        return $this->responseHelper->respond([
            'tier'              => $tier,
            'tier_types'        => $tier_types,
            'billing_durations' => $billing_durations,
        ], 'Tier to edit fetched successfully');
    }


    public function update($data, string $id)
    {
        $validator = Validator::make($data, [
            "name"             => ['required'],
            "description"      => ['required'],
            "benefits"         => ['required'],
            "type"             => ['required'],
            "price"            => ['required'],
            "billing_duration" => ['required'],
            "is_active"        => ['required'],
            "is_recommended"   => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()]);
        }

        $tier   = Tier::where('id', $id)->first();
        $result = $tier->update($data);

        if (! $result) {
            return $this->responseHelper->error('Bad request', [
                'errors'  => $validator->errors(),
                'message' => 'Tier could not be updated',
            ]);
        }

        $tier = Tier::where('id', $id)->first();

        if (!$tier) {
            return $this->responseHelper->error('Tier not found', ['message' => 'No tier found with the given ID']);
        }
        return $this->responseHelper->respond(['tier' => $tier], 'Tier updated successfully');
    }

    public function destroy(string $id)
    {
        $result = Tier::destroy($id);

        if (! $result) {
            return $this->responseHelper->error('Bad request', ['message' => 'Tier could not be deleted']);
        }

        return $this->responseHelper->respond([], 'Tier deleted successfully');
    }

    public function getTierPermissions($tier_id)
    {
        try {
            $tier = Tier::where('id', $tier_id)->first();

            if (!$tier) {
                return $this->responseHelper->error('Tier not found', ['message' => 'No tier found with the given ID']);
            }
            $permissions = $tier->permissions()->get();
            return $this->responseHelper->respond(['permissions' => $permissions], "Permissions fetched for tier", 200);
        } catch (\Exception $e) {
            return $this->responseHelper->error('server error', ['message' => $e->getMessage()], 500);
        }
    }

    public function storeTierPermissions(array $data, string $tier_id)
    {
        $validator = Validator::make($data, [
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()], 400);
        }

        $tier = Tier::where('id', $tier_id)->first();
        if (!$tier) {
            return $this->responseHelper->error('Tier not found', ['message' => 'No tier found with the given ID'], 404);
        }

        try {
            $tier->permissions()->syncWithoutDetaching($data['permission_ids']);
            $permissions = $tier->permissions()->get();
            return $this->responseHelper->respond(['permissions' => $permissions], "Permissions assigned to tier", 201);
        } catch (\Exception $e) {
            return $this->responseHelper->error($e->getMessage(), 500);
        }
    }


    public function removeTierPermissions($tier_id,  $permission_id)
    {
        try {
            $tier = Tier::where('id', $tier_id)->first();
            if (!$tier) {
                return $this->responseHelper->error('Tier not found', ['message' => '
                No tier found with the given ID'], 404);
            }
            $permission = $tier->permissions()->where('id', $permission_id)->first();
            if (!$permission) {
                return $this->responseHelper->error(
                    'Permission not found',
                    ['message' => 'No permission found with the given ID for this tier'],
                    404
                );
            }

            $tier->permissions()->detach($permission->id);
            return $this->responseHelper->respond(null, "Permission removed from tier", 200);
        } catch (\Exception $e) {
            return $this->responseHelper->error($e->getMessage(),['message' => $e->getMessage()], 500);
        }
    }
}

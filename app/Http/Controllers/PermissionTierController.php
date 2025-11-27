<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Tier;
use App\Http\Resources\ApiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionTierController extends BaseController
{
    public function __construct( ApiResource $apiResource)
    {
        parent::__construct($apiResource);
    }

    // Temporarily disable swagger annotation to avoid $ref Permission not found error
    /*
    @OA\Get(
        path="/api/v1/tiers/{tier}/permissions",
        tags={"Tier-Permissions"},
        security={{"bearerAuth":{}}},
        summary="List permissions assigned to a tier",
        description="Get all permissions that are assigned to a specific subscription tier.",
        @OA\Parameter(
            name="tier",
            in="path",
            description="Subscription tier ID",
            required=true,
            @OA\Schema(type="integer")
        ),
        @OA\Response(
            response=200,
            description="List of permissions for the tier",
            @OA\JsonContent(type="object", @OA\Property(property="permissions", type="array",
                @OA\Items(ref="#/components/schemas/Permission")
            ))
        ),
        @OA\Response(response=401, description="Unauthorized"),
        @OA\Response(response=404, description="Tier not found")
    )
    */
    public function index(Tier $tier): JsonResponse
    {
        try {
            $permissions = $tier->permissions()->get();
            return $this->apiResource->success(['permissions' => $permissions], "Permissions fetched for tier", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tiers/{tier}/permissions",
     *     tags={"Tier-Permissions"},
     *     security={{"bearerAuth":{}}},
     *     summary="Assign permissions to a tier",
     *     description="Assign one or more permissions to a specific subscription tier.",
     *     @OA\Parameter(
     *         name="tier",
     *         in="path",
     *         description="Subscription tier ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"permission_ids"},
     *             @OA\Property(
     *                 property="permission_ids",
     *                 type="array",
     *                 @OA\Items(type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Permissions assigned successfully"),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Tier not found")
     * )
     */
    public function store(Request $request, Tier $tier): JsonResponse
    {
        $validated = $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ]);

        try {
            $tier->permissions()->syncWithoutDetaching($validated['permission_ids']);
            $permissions = $tier->permissions()->get();
            return $this->apiResource->success(['permissions' => $permissions], "Permissions assigned to tier", 201);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/tiers/{tier}/permissions/{permission}",
     *     tags={"Tier-Permissions"},
     *     security={{"bearerAuth":{}}},
     *     summary="Remove a permission from a tier",
     *     description="Remove a specific permission from a subscription tier.",
     *     @OA\Parameter(
     *         name="tier",
     *         in="path",
     *         description="Subscription tier ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         description="Permission ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Permission removed successfully"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Tier or Permission not found")
     * )
     */
    public function destroy(Tier $tier, Permission $permission): JsonResponse
    {
        try {
            $tier->permissions()->detach($permission->id);
            return $this->apiResource->success(null, "Permission removed from tier", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }
}

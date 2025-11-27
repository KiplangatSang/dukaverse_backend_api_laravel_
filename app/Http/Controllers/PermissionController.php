<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Services\PermissionsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends BaseController
{
    public function __construct(
        private PermissionsService $permissionsService,
        ApiResource $apiResource
    ) {
         parent::__construct($apiResource);
    }

     /*
    @OA\Get(
        path="/api/v1/user/permissions-list",
        tags={"Permissions"},
        security={{"bearerAuth":{}}},
        summary="List permissions for authenticated user",
        @OA\Response(
            response=200,
            description="Permissions fetched successfully",
            @OA\JsonContent(
                type="object",
                @OA\Property(
                    property="permissions",
                    type="array",
                    @OA\Items(ref="#/components/schemas/Permission")
                )
            )
        ),
        @OA\Response(response=401, description="Unauthorized")
    )
    */
    public function permissionsList(): JsonResponse
    {
        try {
            $account = $this->account();
            $permissions = $this->permissionsService->listPermissions($account);
            // Convert permissions collection to array to avoid merge issues
            return $this->apiResource->success(['permissions' => $permissions->toArray()], "Permissions fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/permissions",
     *     tags={"Permissions"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new permission",
     *     description="Create a new permission for the authenticated user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="edit_post"),
     *             @OA\Property(property="description", type="string", example="Allows editing posts")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Permission created successfully"),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        try {
            $account = $this->account();
            $permission = $this->permissionsService->createPermission($account, $validated);
            return $this->apiResource->success($permission->toArray(), "Permission created successfully", 201);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

     /*
    @OA\Get(
        path="/api/v1/user/permissions/{id}",
        tags={"Permissions"},
        security={{"bearerAuth":{}}},
        summary="Get permission by ID",
        description="Fetch a specific permission by its ID for the authenticated user",
        @OA\Parameter(
            name="id",
            in="path",
            description="Permission ID",
            required=true,
            @OA\Schema(type="integer")
        ),
        @OA\Response(response=200, description="Permission fetched successfully"),
        @OA\Response(response=404, description="Permission not found"),
        @OA\Response(response=401, description="Unauthorized")
    )
    */
    public function show($id)
    {
        try {
            $account = $this->account();
            $permission = $this->permissionsService->listPermissions($account)
                ->where('id', $id)
                ->first();

            if (!$permission) {
                return $this->apiResource->error("Permission not found", 404);
            }

            return $this->apiResource->success($permission->toArray(), "Permission fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /*
    @OA\Put(
        path="/api/v1/user/permissions/{id}",
        tags={"Permissions"},
        security={{"bearerAuth":{}}},
        summary="Update permission by ID",
        description="Update a specific permission for the authenticated user",
        @OA\Parameter(
            name="id",
            in="path",
            description="Permission ID",
            required=true,
            @OA\Schema(type="integer")
        ),
        @OA\RequestBody(
            required=true,
            @OA\JsonContent(
                @OA\Property(property="name", type="string", example="edit_post"),
                @OA\Property(property="description", type="string", example="Allows editing posts")
            )
        ),
        @OA\Response(response=200, description="Permission updated successfully"),
        @OA\Response(response=404, description="Permission not found"),
        @OA\Response(response=401, description="Unauthorized")
    )
    */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string'
        ]);

        try {
            $account = $this->account();
            $permission = $this->permissionsService->listPermissions($account)
                ->where('id', $id)
                ->first();

            if (!$permission) {
                return $this->apiResource->error("Permission not found", 404);
            }

            $updatedPermission = $this->permissionsService->updatePermission($id, $validated);
            return $this->apiResource->success($updatedPermission->toArray(), "Permission updated successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /*
    @OA\Delete(
        path="/api/v1/user/permissions/{id}",
        tags={"Permissions"},
        security={{"bearerAuth":{}}},
        summary="Delete permission by ID",
        description="Delete a specific permission for the authenticated user",
        @OA\Parameter(
            name="id",
            in="path",
            description="Permission ID",
            required=true,
            @OA\Schema(type="integer")
        ),
        @OA\Response(response=200, description="Permission deleted successfully"),
        @OA\Response(response=404, description="Permission not found"),
        @OA\Response(response=401, description="Unauthorized")
    )
    */
    public function destroy($id)
    {
        try {
            $account = $this->account();
            $permission = $this->permissionsService->listPermissions($account)
                ->where('id', $id)
                ->first();

            if (!$permission) {
                return $this->apiResource->error("Permission not found", 404);
            }

            $this->permissionsService->deletePermission($id);

            return $this->apiResource->success(null, "Permission deleted successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }
}

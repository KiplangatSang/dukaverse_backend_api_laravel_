<?php
namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;
use App\Services\AnalyticsService;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class HomeController extends BaseController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly AnalyticsService $analyticsService,
        private readonly \App\Services\PermissionsService $permissionsService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/analytics/dashboard",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get analytics dashboard data",
     *     description="Fetch the main analytics dashboard data",
     *     @OA\Response(
     *         response=200,
     *         description="Data fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status": true, "message": "Data fetched successfully", "data": {"users": 100, "sales": 2000}}
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     */
    public function dashboard(): JsonResponse
    {
         $data = $this->analyticsService->index();
        if (isset($data['error'])) {
            return $this->apiResource->error($data['error'], 400);
        }
        return $this->apiResource->success($data, "Data fetched successfully", 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/permissions-list",
     *     tags={"Permissions"},
     *     security={{"bearerAuth":{}}},
     *     summary="List permissions for authenticated user",
     *     description="Fetch all permissions for the authenticated user",
     *     @OA\Response(
     *         response=200,
     *         description="Permissions fetched successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="permissions", type="array",
     *          @OA\Items(
     *              @OA\Property(property="id", type="integer"),
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="description", type="string", nullable=true),
     *              @OA\Property(property="ownerable_type", type="string"),
     *              @OA\Property(property="ownerable_id", type="integer"),
     *              @OA\Property(property="created_at", type="string", format="date-time"),
     *              @OA\Property(property="updated_at", type="string", format="date-time")
     *          )
     *         ))
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function permissionsList(): \Illuminate\Http\JsonResponse
    {
        try {
            $account = $this->account();
            $permissions = $this->permissionsService->listPermissions($account)->get();
            return $this->apiResource->success(['permissions' => $permissions], "Permissions fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    public function store(\Illuminate\Http\Request $request)
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

    /**
     * @OA\Get(
     *     path="/api/v1/user/permissions/{id}",
     *     tags={"Permissions"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get permission by ID",
     *     description="Fetch a specific permission by its ID for the authenticated user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Permission ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Permission fetched successfully"),
     *     @OA\Response(response=404, description="Permission not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
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

    /**
     * @OA\Put(
     *     path="/api/v1/user/permissions/{id}",
     *     tags={"Permissions"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update permission by ID",
     *     description="Update a specific permission for the authenticated user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Permission ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="edit_post"),
     *             @OA\Property(property="description", type="string", example="Allows editing posts")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Permission updated successfully"),
     *     @OA\Response(response=404, description="Permission not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(\Illuminate\Http\Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string'
        ]);

        try {
            // Check if permission belongs to user
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

    /**
     * @OA\Delete(
     *     path="/api/v1/user/permissions/{id}",
     *     tags={"Permissions"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete permission by ID",
     *     description="Delete a specific permission for the authenticated user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Permission ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Permission deleted successfully"),
     *     @OA\Response(response=404, description="Permission not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
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

    /**
     * @OA\Get(
     *     path="/api/v1/user/permissions",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get permissions",
     *     description="Fetch analytics-related permissions",
     *     @OA\Response(
     *         response=200,
     *         description="Permissions fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status": true, "message": "Permissions fetched successfully", "data": {"permissions": {"view_dashboard": true, "edit_settings": false}}}
     *         )
     *     )
     * )
     */
    public function permissions(): JsonResponse
    {
        try {
             $result = $this->analyticsService->permissions();
            if (isset($result['error'])) {
                return $this->apiResource->error($result['error'], 400);
            }
            return $this->apiResource->success(['permissions' => (array) $result], "Permissions fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/user/data",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get analytics for a user",
     *     description="Fetch user-specific analytics data",
     *     @OA\Response(
     *         response=200,
     *         description="User data fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status": true, "message": "Data fetched successfully", "data": {"id": 1, "name": "John Doe"}}
     *         )
     *     )
     * )
     */
    public function user(): JsonResponse
    {
        try {
             $result = $this->analyticsService->showUser();
            if (isset($data['error'])) {
                return $this->apiResource->error($data['error'], 400);
            }
            return $this->apiResource->success($result["data"], "Data fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/analytics",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get detailed dashboard analytics",
     *     @OA\Response(
     *         response=200,
     *         description="Analytics data fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status": true, "message": "Data fetched successfully", "data": {"visitors": 300, "conversions": 25}}
     *         )
     *     )
     * )
     */
    public function dashboardAnalytics(): JsonResponse
    {
        try {
             $data = $this->analyticsService->dashboardAnalytics();
            if (isset($data['error'])) {
                return $this->apiResource->error($data['error'], 400);
            }
            return $this->apiResource->success($data, "Data fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/analytics/dashboard-projects",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get project analytics",
     *     @OA\Response(
     *         response=200,
     *         description="Project analytics data",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status": true, "message": "Data fetched successfully", "data": {"active_projects": 5, "completed_projects": 10}}
     *         )
     *     )
     * )
     */
    public function dashboardProjects(): JsonResponse
    {
        try {
             $data = $this->analyticsService->dashboardProjects();
            if (isset($data['error'])) {
                return $this->apiResource->error($data['error'], 400);
            }
            return $this->apiResource->success($data, "Data fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/analytics/dashboard-ecommerce",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get e-commerce analytics",
     *     @OA\Response(
     *         response=200,
     *         description="E-commerce analytics data",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status": true, "message": "Data fetched successfully", "data": {"orders": 50, "revenue": 12000}}
     *         )
     *     )
     * )
     */
    public function dashboardEcommerce(): JsonResponse
    {
        try {
             $data = $this->analyticsService->dashboarEcommerce();
            if (isset($data['error'])) {
                return $this->apiResource->error($data['error'], 400);
            }
            return $this->apiResource->success($data, "Data fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/analytics/dashboard-wallet",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get wallet analytics",
     *     @OA\Response(
     *         response=200,
     *         description="Wallet analytics data",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status": true, "message": "Data fetched successfully", "data": {"balance": 5000, "transactions": 20}}
     *         )
     *     )
     * )
     */
    public function dashboardWallet(): JsonResponse
    {
        try {
             $data = $this->analyticsService->dashboadWallet();
            if (isset($data['error'])) {
                return $this->apiResource->error($data['error'], 400);
            }
            return $this->apiResource->success($data, "Data fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/v1/analytics/dashboard-crm",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get CRM analytics",
     *     @OA\Response(
     *         response=200,
     *         description="CRM analytics data",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status": true, "message": "Data fetched successfully", "data": {"leads": 30, "conversions": 15}}
     *         )
     *     )
     * )
     */

    public function dashboardCRM(): JsonResponse
    {
        try {
             $data = $this->analyticsService->dashboardCRM();
            if (isset($data['error'])) {
                return $this->apiResource->error($data['error'], 400);
            }
            return $this->apiResource->success($data, "Data fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }
}

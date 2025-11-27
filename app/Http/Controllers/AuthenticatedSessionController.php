<?php
namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends BaseController
{
    /**
     * @var AuthService
     */
    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/authenticated-session/logout",
     *     tags={"Authentication"},
     *     summary="Log out the authenticated user",
     *     description="Terminates the user's session and invalidates their token.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="message", type="string", example="Logged out successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Logout failed due to invalid state",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error occurred")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error during logout",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */

    public function logout(Request $request)
    {
        try {
            $result = $this->authService->logout(Auth::user());

            if (isset($result['error'])) {
                return $this->apiResource->error($result['message'] ?? 'Error occurred', $result['httpCode'] ?? 400);
            }

            auth()->guard('web')->logout();

            return $this->apiResource->success([], 'Logged out successfully.', 200);
        } catch (Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }
}

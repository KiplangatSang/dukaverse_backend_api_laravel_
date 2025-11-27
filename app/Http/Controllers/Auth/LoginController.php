<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends BaseController
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Login user",
     *     description="Authenticates a user and returns an access token if successful.",
     *     operationId="loginUser",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhb...")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid credentials or error occurred",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid email or password"),
     *             @OA\Property(property="error", type="string", example="Authentication failed")
     *         )
     *     )
     * )
     */

    public function login(Request $request)
    {

        try {
            $result = $this->authService->loginRequest($request->all());

            if (isset($result['error'])) {
                return $this->sendError($result['message'] ?? 'Error occurred', $result['httpCode'] ?? 400);
            }

            $result = $this->authService->saveUserToken(Auth::id());
            if (isset($result['error'])) {
                return $this->sendError($result['message'] ?? 'Error occurred', $result['httpCode'] ?? 400);
            }

            return $this->sendResponse($result['data'], 'Login successful', 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }

    }

/**
 * @OA\Post(
 *     path="/api/v1/logout",
 *     summary="Logout the authenticated user",
 *     description="Logs out the current user by invalidating their token/session.",
 *     operationId="logoutUser",
 *     tags={"Authentication"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Logout successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Logout successful"),
 *             @OA\Property(property="data", type="object", example={})
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Client error during logout",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Invalid token"),
 *             @OA\Property(property="errors", type="object", example=null)
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Server error"),
 *             @OA\Property(property="errors", type="object", example=null)
 *         )
 *     )
 * )
 */

    public function logout(Request $request)
    {
        try {
            $result = $this->authService->logout($request->user());

            if (isset($result['error'])) {
                return $this->apiResource->error($result['message'] ?? 'Error occurred', $result['httpCode'] ?? 400);
            }

            return $this->apiResource->success($result['data'], 'Logout successful', 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

}

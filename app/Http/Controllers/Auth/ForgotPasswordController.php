<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    /**
     * @OA\Post(
     *     path="/api/v1/forgot-password",
     *     summary="Request a password reset link",
     *     description="Sends a password reset link to the user's email address.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","platform_id"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="platform_id", type="string", format="integer", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password reset link sent to your email.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Failed to send reset link",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="We can't find a user with that email address.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error (invalid or missing email)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The email field is required."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="The email must be a valid email address.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'platform_id' => 'required|integer|exists:platforms,id',
        ]);

        // Get the platform URL
        $platform = Platform::find($request->platform_id);
        $platformUrl = $platform->app_url ?? $platform->website ?? config('app.frontend_url');

        // Send reset link to the email with platform URL
        $status = Password::sendResetLink(
            $request->only('email'),
            function ($user, $token) use ($platformUrl) {
                $user->sendPasswordResetNotification($token, $platformUrl);
            }
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent to your email.',
            ], 200);
        }

        return response()->json([
            'error' => __($status),
        ], 400);
    }

}

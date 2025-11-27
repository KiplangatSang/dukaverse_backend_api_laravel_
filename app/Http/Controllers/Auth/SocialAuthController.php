<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SocialAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

/**
 * @OA\Tag(
 *     name="Social Authentication",
 *     description="API endpoints for social media authentication"
 * )
 */
class SocialAuthController extends Controller
{
    protected $socialAuthService;

    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    /**
     * Get social provider authorization URL
     *
     * @OA\Get(
     *     path="/api/v1/auth/{provider}",
     *     tags={"Social Authentication"},
     *     summary="Get social provider authorization URL",
     *     description="Returns the authorization URL for the specified social provider",
     *     operationId="redirectToProvider",
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         description="Social provider (google, facebook, twitter, github, gitlab)",
     *         @OA\Schema(type="string", enum={"google", "facebook", "twitter", "github", "gitlab"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authorization URL returned",
     *         @OA\JsonContent(
     *             @OA\Property(property="authorization_url", type="string"),
     *             @OA\Property(property="provider", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid provider",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid provider")
     *         )
     *     )
     * )
     */
    public function redirectToProvider($provider)
    {
        $validProviders = ['google', 'facebook', 'twitter', 'github', 'gitlab'];

        if (!in_array($provider, $validProviders)) {
            return response()->json(['error' => 'Invalid provider'], 400);
        }

        try {
            $url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();

            return response()->json([
                'authorization_url' => $url,
                'provider' => $provider
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate authorization URL'], 500);
        }
    }

    /**
     * Handle social provider callback
     *
     * @OA\Get(
     *     path="/api/v1/auth/{provider}/callback",
     *     tags={"Social Authentication"},
     *     summary="Handle social provider OAuth callback",
     *     description="Handles the callback from social provider after authentication",
     *     operationId="handleProviderCallback",
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         description="Social provider (google, facebook, twitter, github, gitlab)",
     *         @OA\Schema(type="string", enum={"google", "facebook", "twitter", "github", "gitlab"})
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to frontend with token or error"
     *     )
     * )
     */
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();

            // Validate social user data
            if (!$socialUser->getId() || !$socialUser->getEmail()) {
                throw new \Exception('Invalid social user data received from provider.');
            }

            $user = $this->socialAuthService->findOrCreateUser($socialUser, $provider);

            // Generate Sanctum token
            $token = $user->createToken('social-auth-token')->plainTextToken;

            info("Social login successful for {$provider}: user {$user->id}");

            // Redirect to frontend with token
            $frontendUrl = config('app.frontend_url') . '/auth/callback?token=' . $token . '&provider=' . $provider;

            return redirect($frontendUrl);

        } catch (\Exception $e) {
            info("Social login failed for {$provider}: " . $e->getMessage());

            // Redirect to frontend with error
            $frontendUrl = config('app.frontend_url') . '/auth/callback?error=' . urlencode($e->getMessage());

            return redirect($frontendUrl);
        }
    }

    /**
     * Link social account to existing user
     *
     * @OA\Post(
     *     path="/api/v1/auth/{provider}/link",
     *     tags={"Social Authentication"},
     *     summary="Link social account to authenticated user",
     *     description="Links a social media account to the currently authenticated user",
     *     operationId="linkSocialAccount",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         description="Social provider (google, facebook, twitter, github, gitlab)",
     *         @OA\Schema(type="string", enum={"google", "facebook", "twitter", "github", "gitlab"})
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="string", description="OAuth authorization code")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Social account linked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Google account linked successfully"),
     *             @OA\Property(property="linked_accounts", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error linking account",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function linkSocialAccount(Request $request, $provider)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $validProviders = ['google', 'facebook', 'twitter', 'github', 'gitlab'];

        if (!in_array($provider, $validProviders)) {
            return response()->json(['error' => 'Invalid provider'], 400);
        }

        try {
            $user = Auth::user();

            $socialUser = Socialite::driver($provider)->stateless()->userFromToken($request->code);

            // Validate social user data
            if (!$socialUser->getId() || !$socialUser->getEmail()) {
                return response()->json(['error' => 'Invalid social account data received. Please try again.'], 400);
            }

            $this->socialAuthService->linkSocialAccount($user, $socialUser, $provider);

            return response()->json([
                'message' => ucfirst($provider) . ' account linked successfully',
                'linked_accounts' => $this->socialAuthService->getLinkedAccounts($user)
            ]);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            // Provide user-friendly error messages
            if (str_contains($errorMessage, 'already linked')) {
                $errorMessage = 'This social account is already linked to your account.';
            } elseif (str_contains($errorMessage, 'already exists')) {
                $errorMessage = 'An account with this email already exists. Please use a different social account.';
            }

            return response()->json(['error' => $errorMessage], 400);
        }
    }

    /**
     * Unlink social account
     *
     * @OA\Delete(
     *     path="/api/v1/auth/{provider}/unlink",
     *     tags={"Social Authentication"},
     *     summary="Unlink social account from authenticated user",
     *     description="Removes the link between a social media account and the authenticated user",
     *     operationId="unlinkSocialAccount",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         description="Social provider (google, facebook, twitter, github, gitlab)",
     *         @OA\Schema(type="string", enum={"google", "facebook", "twitter", "github", "gitlab"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Social account unlinked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Google account unlinked successfully"),
     *             @OA\Property(property="linked_accounts", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error unlinking account",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function unlinkSocialAccount($provider)
    {
        $validProviders = ['google', 'facebook', 'twitter', 'github', 'gitlab'];

        if (!in_array($provider, $validProviders)) {
            return response()->json(['error' => 'Invalid provider'], 400);
        }

        try {
            $user = Auth::user();

            $this->socialAuthService->unlinkSocialAccount($user, $provider);

            return response()->json([
                'message' => ucfirst($provider) . ' account unlinked successfully',
                'linked_accounts' => $this->socialAuthService->getLinkedAccounts($user)
            ]);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            // Provide user-friendly error messages
            if (str_contains($errorMessage, 'not linked')) {
                $errorMessage = 'This social account is not linked to your account.';
            } elseif (str_contains($errorMessage, 'set a password')) {
                $errorMessage = 'Please set a password for your account before unlinking your social account.';
            }

            return response()->json(['error' => $errorMessage], 400);
        }
    }

    /**
     * Get linked social accounts
     *
     * @OA\Get(
     *     path="/api/v1/auth/linked-accounts",
     *     tags={"Social Authentication"},
     *     summary="Get linked social accounts for authenticated user",
     *     description="Returns a list of social media accounts linked to the authenticated user",
     *     operationId="getLinkedAccounts",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of linked accounts",
     *         @OA\JsonContent(
     *             @OA\Property(property="linked_accounts", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="provider", type="string", example="google"),
     *                     @OA\Property(property="provider_id", type="string"),
     *                     @OA\Property(property="avatar", type="string", nullable=true),
     *                     @OA\Property(property="linked_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getLinkedAccounts()
    {
        $user = Auth::user();

        return response()->json([
            'linked_accounts' => $this->socialAuthService->getLinkedAccounts($user)
        ]);
    }
}

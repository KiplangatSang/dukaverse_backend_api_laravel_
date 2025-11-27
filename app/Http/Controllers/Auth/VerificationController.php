<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Helpers\Sms\SmsMessage as SmsHelper;
use Illuminate\Support\Str;

class VerificationController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    // use VerifiesEmails;

    // /**
    //  * Where to redirect users after verification.
    //  *
    //  * @var string
    //  */
    // protected $redirectTo = RouteServiceProvider::HOME;

    // /**
    //  * Create a new controller instance.
    //  *
    //  * @return void
    //  */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('signed')->only('verify');
    //     $this->middleware('throttle:6,1')->only('verify', 'resend');
    // }

    // public function verify(Request $request, $id, $hash)
    // {
    //     $user = User::findOrFail($id);

    //     if (! Hash::check($hash, $user->getEmailForVerificationToken())) {
    //         return response()->json(['message' => 'Invalid verification token.'], 400);
    //     }

    //     $user->markEmailAsVerified();
    //     event(new Verified($user)); // Fire the verified event

    //     return response()->json(['message' => 'Your email has been verified successfully.']);
    // }

    /**
     * @OA\Post(
     *     path="/api/email/verify/{id}/{hash}",
     *     summary="Verify user email",
     *     description="Verify a user's email address using the verification link sent via email",
     *     operationId="verifyEmail",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         required=true,
     *         description="Verification hash",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email verified successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid verification token",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid verification token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Check if the hash matches the SHA-1 of the user's email.
        // This assumes that your User model implements MustVerifyEmail, which provides getEmailForVerification().
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification token.'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/email/resend",
     *     summary="Resend email verification",
     *     description="Resend the email verification notification to the authenticated user",
     *     operationId="resendEmailVerification",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Verification email sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Verification email has been sent!"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="message", type="string", example="Verification email has been sent!")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=501,
     *         description="Email already verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You have already verified your email."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->sendError('You have already verified your email.', ["user" => $user, "message" => 'You have already verified your email.'], 501);
        }

        $user->sendEmailVerificationNotification();

        return $this->sendResponse(["user" => $user, 'message' => 'Verification email has been sent!'], 'Verification email has been sent!', 200);

    }

    /**
     * @OA\Post(
     *     path="/api/phone/send-otp",
     *     summary="Send OTP for phone verification",
     *     description="Send a 6-digit OTP to the user's phone number for verification",
     *     operationId="sendPhoneOtp",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP sent successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string", example="OTP sent successfully")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Phone already verified or not provided",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Phone number already verified."),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send OTP",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to send OTP."),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function sendOtp(Request $request)
    {
        $user = $request->user();

        if ($user->phone_verified_at) {
            return $this->sendError('Phone number already verified.', [], 400);
        }

        if (!$user->phone_number) {
            return $this->sendError('Phone number not provided.', [], 400);
        }

        $otp = Str::random(6);
        $cacheKey = 'phone_otp_' . $user->id;

        Cache::put($cacheKey, $otp, now()->addMinutes(5));

        // Send OTP via SMS
        $sms = new SmsHelper();
        $sms->to($user->phone_number)->line("Your verification code is: $otp");
        $result = $sms->send();

        if ($result !== true) {
            return $this->sendError('Failed to send OTP.', [], 500);
        }

        return $this->sendResponse(['message' => 'OTP sent successfully'], 'OTP sent successfully', 200);
    }

    /**
     * @OA\Post(
     *     path="/api/phone/verify-otp",
     *     summary="Verify phone OTP",
     *     description="Verify the OTP sent to the user's phone number",
     *     operationId="verifyPhoneOtp",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"otp"},
     *             @OA\Property(property="otp", type="string", example="123456", description="6-digit OTP code")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Phone number verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Phone number verified successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string", example="Phone number verified successfully")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired OTP",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid or expired OTP."),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    /**
     * @OA\Post(
     *     path="/api/email/verify-code",
     *     summary="Verify email using code",
     *     description="Verify user's email address using the 6-digit code sent via email",
     *     operationId="verifyEmailCode",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code"},
     *             @OA\Property(property="code", type="string", example="ABC123", description="6-character verification code")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Email verified successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string", example="Email verified successfully")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired code",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid or expired verification code."),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function verifyEmailCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = $request->user();
        $cacheKey = 'email_verification_code_' . $user->id;
        $cachedCode = Cache::get($cacheKey);

        if (!$cachedCode || $cachedCode !== $request->code) {
            return $this->sendError('Invalid or expired verification code.', [], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->sendResponse(['message' => 'Email already verified'], 'Email already verified', 200);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        Cache::forget($cacheKey);

        return $this->sendResponse(['message' => 'Email verified successfully'], 'Email verified successfully', 200);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        $user = $request->user();
        $cacheKey = 'phone_otp_' . $user->id;
        $cachedOtp = Cache::get($cacheKey);

        if (!$cachedOtp || $cachedOtp !== $request->otp) {
            return $this->sendError('Invalid or expired OTP.', [], 400);
        }

        $user->phone_verified_at = now();
        $user->save();

        Cache::forget($cacheKey);

        return $this->sendResponse(['message' => 'Phone number verified successfully'], 'Phone number verified successfully', 200);
    }
}

<?php
namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Profile",
 *     description="User profile management endpoints"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class ProfileController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/profile",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get authenticated user's profile",
     *     @OA\Response(
     *         response=200,
     *         description="Profile data fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=42),
     *                 @OA\Property(property="name", type="string", example="Jane Doe"),
     *                 @OA\Property(property="email", type="string", example="jane.doe@example.com"),
     *                 @OA\Property(property="email_verified_at", type="string", format="date-time", example="2025-09-20T14:30:00Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-10T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-25T08:00:00Z")
     *             ),
     *             @OA\Property(
     *                 property="profile",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=101),
     *                 @OA\Property(property="user_id", type="integer", example=42),
     *                 @OA\Property(property="phone", type="string", example="+1-202-555-0143"),
     *                 @OA\Property(property="address", type="string", example="123 Main St, Springfield, USA"),
     *                 @OA\Property(property="avatar", type="string", example="/uploads/avatars/jane.png"),
     *                 @OA\Property(property="bio", type="string", example="Full-stack developer with a love for open-source."),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T09:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-24T17:00:00Z")
     *             )
     *         )
     *     )
     * )
     */

    public function index()
    {
        $user    = $this->user();
        $profile = $user->profile;

        return $this->sendResponse(
            ["user" => $user, "profile" => $profile],
            "Data fetched successfully"
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/profile",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     summary="Upload profile picture",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"profile_image"},
     *                 @OA\Property(property="profile_image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile picture uploaded successfully")
     * )
     */
    public function store(Request $request)
    {
        $user = $this->user();

        $validator = Validator::make($request->all(), [
            'profile_image' => ['sometimes', 'file', 'mimes:jpg,bmp,png'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ["errors" => $validator->errors()]);
        }

        $profile_image_url = $this->saveFile(User::PROFILE_FOLDER . Auth::id(), $request->file('profile_image'));

        if (! $profile_image_url) {
            return $this->sendError("Bad request", ["error" => "Could not save the profile picture"]);
        }

        $update = $user->profile()->update(["profile_image" => $profile_image_url]);

        if (! $update) {
            return $this->sendError("Bad request", ["error" => "Could not update the profile picture"]);
        }

        return $this->sendResponse(["user" => $user, "profile" => $user->profile], "User profile successfully updated");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/profile/update-picture",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update profile picture",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"profile_image"},
     *                 @OA\Property(property="profile_image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile picture updated successfully")
     * )
     */
    public function updateProfilePicture(Request $request)
    {
        $user = $this->user();

        $validator = Validator::make($request->all(), [
            'profile_image' => ['required', 'file', 'mimes:jpg,bmp,png'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ["errors" => $validator->errors()]);
        }

        $profile_image_url = $this->saveFile(User::PROFILE_FOLDER . Auth::id(), $request->file('profile_image'));

        if (! $profile_image_url) {
            return $this->sendError("Bad request", ["error" => "Could not save the profile picture"]);
        }

        $update = $user->profile()->update(["profile_image" => $profile_image_url]);

        if (! $update) {
            return $this->sendError("Bad request", ["error" => "Could not update the profile picture"]);
        }

        return $this->sendResponse(["user" => $user, "profile" => $user->profile], "User profile updated successfully");
    }

    /**
     * @OA\Put(
     *     path="/api/v1/profile",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update profile details",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"profile_updates","user_updates"},
     *             @OA\Property(property="profile_updates", type="object"),
     *             @OA\Property(property="user_updates", type="object")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile updated successfully")
     * )
     */
    public function update(UpdateProfileRequest $request, Profile $profile)
    {
        $user = $this->user();

        $validator = Validator::make($request->all(), [
            'profile_updates' => 'required|array',
            'user_updates'    => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ["errors" => $validator->errors()]);
        }

        $updatedProfile = $user->profile()->update($request->profile_updates);
        $updatedUser    = $user->update($request->user_updates);

        if (! $updatedProfile || ! $updatedUser) {
            return $this->sendError("Bad request", ["error" => "Could not update the profile"]);
        }

        return $this->sendResponse(["user" => $user, "profile" => $user->profile], "User profile updated successfully");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/profile/{id}/documents",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     summary="Upload user documents",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(@OA\Property(property="file", type="string", format="binary"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Documents uploaded successfully")
     * )
     */
    public function uploadUserDocuments($id, Request $request)
    {
        $user    = User::findOrFail(Auth::id());
        $profile = $user->profile;

        $fileNameToStore = $this->getBaseImages()['nofile'];

        if ($request->hasFile('file')) {
            $fileNameToStore = $this->saveFile("national_id", $request->file('file'));
        }

        $documents                = $profile->national_id ? (array) json_decode($profile->national_id) : [];
        $documents['national_id'] = $fileNameToStore;

        $profile->update(["national_id" => json_encode($documents)]);

        return $this->sendResponse(["profile" => $profile], "Document uploaded successfully");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/profile/{id}/relevant-documents",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     summary="Upload relevant documents",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(@OA\Property(property="file", type="string", format="binary"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Relevant documents uploaded successfully")
     * )
     */
    public function uploadRelevantDocuments($id, Request $request)
    {
        $user    = User::findOrFail(Auth::id());
        $profile = $user->profile;

        $fileNameToStore = $this->getBaseImages()['nofile'];

        if ($request->hasFile('file')) {
            $fileNameToStore = $this->saveFile("relevant_documents", $request->file('file'));
        }

        $documents = $profile->relevant_documents
            ? (array) json_decode($profile->relevant_documents)
            : [];

        $documents[] = $fileNameToStore;

        $profile->update(["relevant_documents" => json_encode($documents)]);

        return $this->sendResponse(["profile" => $profile], "Relevant documents uploaded successfully");
    }
}

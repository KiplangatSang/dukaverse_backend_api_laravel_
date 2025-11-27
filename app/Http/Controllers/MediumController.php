<?php
namespace App\Http\Controllers;

use App\Helpers\Accounts\Account;
use App\Models\Medium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Media",
 *     description="Manage media files and uploads"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class MediumController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/media",
     *     summary="List all media for the authenticated user",
     *     tags={"Media"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Media fetched successfully"
     *     )
     * )
     */
    public function index()
    {
        $media = $this->user()->media;

        return $this->sendResponse(["media" => $media],
            'success, medias fetched successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/media/create",
     *     summary="Get available file owners (fileable types)",
     *     tags={"Media"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="File owners fetched successfully"
     *     )
     * )
     */
    public function create()
    {
        $file_owners = Medium::FILEABLES;

        return $this->sendResponse(["file_owners" => $file_owners],
            'success, medias fetched successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/media",
     *     summary="Upload and store a new media file",
     *     tags={"Media"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file_owner", "file", "name"},
     *                 @OA\Property(property="file_owner", type="string", example="user"),
     *                 @OA\Property(property="file", type="string", format="binary"),
     *                 @OA\Property(property="name", type="string", example="Profile Picture")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Media saved successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "file_owner" => ["required"],
            "file"       => ["required"],
            "name"       => ["required"],
        ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The file could not be saved .", "result" => $validator->errors()]);
        }

        $validatated = $validator->validated();

        $file_url = $this->saveFile("media", $validatated["file"]);

        $file_details = Medium::getFileDetails($request->file);
        $file_medium  = Medium::createMediumFormat($file_url, $file_details["type"],
            $file_details["name"], $file_details["size"], $file_details["resolution"]);

        $fileOwner = $this->user();

        if ($request->file_owner == Medium::ACCOUNT_FILEABLE) {
            $fileOwner = $this->account();
        }

        $result = $fileOwner->media()->udpateOrCreate([
            $file_medium,
            ['user_id' => $this->user()->id],
        ]);

        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The file media could not be saved.", "result" => $result]);
        }

        return $this->sendResponse(["medium" => $result], "success, The file media has been saved successfully.");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/media/{medium}",
     *     summary="Get a specific media file",
     *     tags={"Media"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="medium",
     *         in="path",
     *         required=true,
     *         description="Media ID",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Media fetched successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Media not found"
     *     )
     * )
     */
    public function show($medium)
    {
        $medium = Medium::where('id', $medium)->first();

        if (! $medium) {
            return $this->sendError("Bad request", ["error" => "The file media could not be fetched.", "result" => $medium]);
        }

        return $this->sendResponse(["medium" => $medium], "success, The file media has been fetched successfully.");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/media/{medium}/edit",
     *     summary="Fetch media and file owners for editing",
     *     tags={"Media"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="medium",
     *         in="path",
     *         required=true,
     *         description="Media ID",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Media and file owners fetched successfully"
     *     )
     * )
     */
    public function edit($medium)
    {
        $medium      = Medium::where('id', $medium)->first();
        $file_owners = Medium::FILEABLES;

        if (! $medium) {
            return $this->sendError("Bad request", ["error" => "The file media could not be fetched.", "result" => $medium]);
        }

        return $this->sendResponse(["medium" => $medium, "file_owners" => $file_owners], "success, The file media has been fetched successfully.");
    }

    /**
     * @OA\Put(
     *     path="/api/v1/media/{medium}",
     *     summary="Update an existing media file",
     *     tags={"Media"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="medium",
     *         in="path",
     *         required=true,
     *         description="Media ID",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file_owner", "file", "name"},
     *                 @OA\Property(property="file_owner", type="string", example="user"),
     *                 @OA\Property(property="file", type="string", format="binary"),
     *                 @OA\Property(property="name", type="string", example="Updated Picture")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Media updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Media not found"
     *     )
     * )
     */
    public function update(Request $request, $medium)
    {
        $validator = Validator::make($request->all(), [
            "file_owner" => ["required"],
            "file"       => ["required"],
            "name"       => ["required"],
        ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The file could not be saved .", "result" => $validator->errors()]);
        }

        $validatated = $validator->validated();

        $medium = Medium::where('id', $medium)->first();

        if (! $medium) {
            return $this->sendError("Bad request", ["error" => "The file media could not be fetched.", "result" => $medium]);
        }

        $file_url = $this->saveFile("media", $validatated["file"]);

        $file_details = Medium::getFileDetails($request->file);
        $file_medium  = Medium::createMediumFormat($file_url, $file_details["type"],
            $file_details["name"], $file_details["size"], $file_details["resolution"]);

        $fileOwner = $this->user();

        if ($request->file_owner == Medium::ACCOUNT_FILEABLE) {
            $fileOwner = $this->account();
        }

        $file_medium['user_id']         = $this->user()->id;
        $file_medium['mediumable_id']   = $fileOwner->id;
        $file_medium['mediumable_type'] = Account::getAccountInstance($fileOwner);

        $result = $medium->udpate($file_medium);

        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The file media could not be saved.", "result" => $result]);
        }

        $medium = Medium::where('id', $medium)->first();

        return $this->sendResponse(["medium" => $medium], "success, The file media has been saved successfully.");
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/media/{medium}",
     *     summary="Delete a media file",
     *     tags={"Media"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="medium",
     *         in="path",
     *         required=true,
     *         description="Media ID",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Media deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Media not found"
     *     )
     * )
     */
    public function destroy(Medium $medium)
    {
        $result = Medium::destroy($medium);
        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The file media could not be deleted.", "result" => $result]);
        }
        return $this->sendResponse(["result" => $result], "success, The file media has been deleted successfully.");
    }
}

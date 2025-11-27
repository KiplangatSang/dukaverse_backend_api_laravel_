<?php
namespace App\Http\Controllers;

use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Comments",
 *     description="API Endpoints for managing user comments on projects and tasks"
 * )
 */
class CommentController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/comments",
     *     summary="Get all comments for the authenticated user",
     *     description="Fetch all comments belonging to the authenticated user. Optionally filter by comment type (project_comments or task_comments).",
     *     security={{"bearerAuth":{}}},
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter comments by type: project_comments | task_comments",
     *         required=false,
     *         @OA\Schema(type="string", enum={"project_comments","task_comments"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comments fetched successfully",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function index($type = null)
    {
        $all_comments     = $this->user()->comments;
        $project_comments = $all_comments->where('commentable_type', Comment::COMMENTABLE_TYPES["project_comments"]);
        $task_comments    = $all_comments->where('commentable_type', Comment::COMMENTABLE_TYPES["task_comments"]);

        if ($type === "project_comments") {
            return $this->sendResponse(["project_comments" => $project_comments], 'success, comments fetched successfully');
        }

        if ($type === "task_comments") {
            return $this->sendResponse(["task_comments" => $task_comments], 'success, comments fetched successfully');
        }

        return $this->sendResponse([
            "all_comments"     => $all_comments,
            "project_comments" => $project_comments,
            "task_comments"    => $task_comments,
        ], 'success, comments fetched successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comments/create",
     *     summary="Get available comment types",
     *     description="Returns all available commentable types for creating a comment.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Comments"},
     *     @OA\Response(
     *         response=200,
     *         description="Comment types fetched successfully"
     *     )
     * )
     */
    public function create()
    {
        $comment_types = Comment::COMMENTABLES;
        return $this->sendResponse(["comment_types" => $comment_types], 'success, comments types fetched successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comments",
     *     summary="Create a new comment",
     *     description="Creates a new comment associated with a project or task.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Comments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"commentable_type","commentable_id","content"},
     *             @OA\Property(property="parent_comment_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="commentable_type", type="string", example="project_comments"),
     *             @OA\Property(property="commentable_id", type="integer", example=10),
     *             @OA\Property(property="content", type="string", example="This is my comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment created successfully"
     *     ),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "commentable_type" => "required",
            "commentable_id"   => "required",
            "content"          => ["required", "string"],
        ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The comment could not be saved.", "result" => $validator->errors()]);
        }

        $validatated = $validator->validated();

        $comment = $this->user()->comments()->create([
            "parent_id"        => $request->parent_comment_id,
            "commentable_type" => $request->commentable_type ? Comment::COMMENTABLE_TYPES[$request->commentable_type] : null,
            "commentable_id"   => $validatated['commentable_id'],
            "content"          => $validatated['content'],
            "user_id"          => Auth::id(),
        ]);

        if (! $comment) {
            return $this->sendError("Bad request", ["error" => "The comment could not be saved.", "result" => $comment]);
        }

        return $this->sendResponse(["comment" => $comment], "success, The comment has been saved successfully.");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comments/{id}",
     *     summary="Get a specific comment",
     *     description="Retrieve details of a single comment by ID.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Comment fetched successfully"),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function show($comment)
    {
        $comment = $this->user()->comments()->where('id', $comment)->first();
        if (! $comment) {
            return $this->sendError("Bad request", ["error" => "The comment could not be found.", "result" => $comment]);
        }
        return $this->sendResponse(["comment" => $comment], "success, The comment has been fetched successfully.");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comments/{id}/edit",
     *     summary="Edit a comment",
     *     description="Get a comment with available types for editing.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Comment and types fetched successfully"),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function edit($comment)
    {
        $comment = $this->user()->comments()->where('id', $comment)->first();
        if (! $comment) {
            return $this->sendError("Bad request", ["error" => "The comment could not be found.", "result" => $comment]);
        }

        $comment_types = Comment::COMMENTABLES;

        return $this->sendResponse(["comment" => $comment, "comment_types" => $comment_types], "success, The comment has been fetched successfully.");
    }

    /**
     * @OA\Put(
     *     path="/api/v1/comments/{id}",
     *     summary="Update a comment",
     *     description="Update an existing comment by ID.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"commentable_type","commentable_id","content"},
     *             @OA\Property(property="commentable_type", type="string", example="task_comments"),
     *             @OA\Property(property="commentable_id", type="integer", example=11),
     *             @OA\Property(property="content", type="string", example="Updated comment text")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Comment updated successfully"),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $validator = Validator::make($request->all(), [
            "commentable_type" => "required",
            "commentable_id"   => "required",
            "content"          => ["required", "string"],
        ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The comment could not be saved.", "result" => $validator->errors()]);
        }

        $validatated = $validator->validated();
        $comment     = $this->user()->comments()->where('id', $comment)->first();

        $result = $comment->update([
            "parent_id"        => $request->parent_id,
            "commentable_type" => $validatated['commentable_type'],
            "commentable_id"   => $validatated['commentable_id'],
            "content"          => $validatated['content'],
        ]);

        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The comment could not be saved.", "result" => $comment]);
        }

        $comment = $this->user()->comments()->where('id', $comment)->first();
        return $this->sendResponse(["comment" => $comment], "success, The comment has been saved successfully.");
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/comments/{id}",
     *     summary="Delete a comment",
     *     description="Delete a comment by ID.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Comment deleted successfully"),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function destroy($comment)
    {
        $result = Comment::destroy($comment);
        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The comment could not be deleted.", "result" => $result]);
        }
        return $this->sendResponse(["comment" => $result], "success, The comment has been deleted successfully.");
    }
}

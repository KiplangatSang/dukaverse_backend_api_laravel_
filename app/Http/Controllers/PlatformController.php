<?php
namespace App\Http\Controllers;

use App\Http\Requests\StorePlatformRequest;
use App\Http\Requests\UpdatePlatformRequest;
use App\Models\Platform;

class PlatformController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/platforms",
     *     summary="Get list of platforms",
     *     description="Retrieve all platforms with their related users, ecommerces, and locations.",
     *     tags={"Platforms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Platforms retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Platforms retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Shopify"),
     *                     @OA\Property(property="slug", type="string", example="shopify"),
     *                     @OA\Property(property="logo", type="string", example="https://example.com/logo.png"),
     *                     @OA\Property(property="description", type="string", example="E-commerce platform"),
     *                     @OA\Property(property="website", type="string", example="https://shopify.com"),
     *                     @OA\Property(property="app_url", type="string", example="https://app.shopify.com"),
     *                     @OA\Property(property="contact_email", type="string", example="support@shopify.com"),
     *                     @OA\Property(property="contact_phone", type="string", example="+1 800 123 456"),
     *                     @OA\Property(property="address", type="string", example="123 Shopify St, Ottawa, Canada"),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="is_default", type="boolean", example=false),
     *                     @OA\Property(property="created_by", type="string", example="admin"),
     *                     @OA\Property(property="updated_by", type="string", example="editor"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $platforms = Platform::with(['users', 'ecommerces', 'locations'])->get();
        return response()->json([
            'status'  => 'success',
            'message' => 'Platforms retrieved successfully.',
            'data'    => $platforms,
        ]);
    }

    public function create()
    {
        return response()->json([
            'status'  => 'success',
            'message' => 'Platform creation form.',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/platforms",
     *     summary="Create a new platform",
     *     description="Store a new platform record in the database.",
     *     tags={"Platforms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name","slug"},
     *             @OA\Property(property="name", type="string", example="Shopify"),
     *             @OA\Property(property="slug", type="string", example="shopify"),
     *             @OA\Property(property="logo", type="string", example="https://example.com/logo.png"),
     *             @OA\Property(property="description", type="string", example="E-commerce platform"),
     *             @OA\Property(property="website", type="string", example="https://shopify.com"),
     *             @OA\Property(property="app_url", type="string", example="https://app.shopify.com"),
     *             @OA\Property(property="contact_email", type="string", example="support@shopify.com"),
     *             @OA\Property(property="contact_phone", type="string", example="+1 800 123 456"),
     *             @OA\Property(property="address", type="string", example="123 Shopify St, Ottawa, Canada"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="is_default", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Platform created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Platform created successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Shopify"),
     *                 @OA\Property(property="slug", type="string", example="shopify")
     *             )
     *         )
     *     )
     * )
     */
    public function store(StorePlatformRequest $request)
    {

        $platform = Platform::create($request->validated());
        return response()->json([
            'status'  => 'success',
            'message' => 'Platform created successfully.',
            'data'    => $platform,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/platforms/{id}",
     *     summary="Get platform details",
     *     description="Retrieve a specific platform by ID, including its related users, ecommerces, and locations.",
     *     tags={"Platforms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Platform ID", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Platform retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Platform retrieved successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Shopify"),
     *                 @OA\Property(property="slug", type="string", example="shopify")
     *             )
     *         )
     *     )
     * )
     */
    public function show(Platform $platform)
    {
        return response()->json([
            'status'  => 'success',
            'message' => 'Platform retrieved successfully.',
            'data'    => $platform->load(['users', 'ecommerces', 'locations']),
        ]);
    }

    public function edit(Platform $platform)
    {
        return response()->json([
            'status'  => 'success',
            'message' => 'Platform edit form.',
            'data'    => $platform,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/platforms/{id}",
     *     summary="Update platform",
     *     description="Update the specified platform record.",
     *     tags={"Platforms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Platform ID", @OA\Schema(type="integer", example=1)),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Shopify Updated"),
     *             @OA\Property(property="slug", type="string", example="shopify-updated"),
     *             @OA\Property(property="is_active", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Platform updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Platform updated successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Shopify Updated"),
     *                 @OA\Property(property="slug", type="string", example="shopify-updated")
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdatePlatformRequest $request, Platform $platform)
    {
        $platform->update($request->validated());
        return response()->json([
            'status'  => 'success',
            'message' => 'Platform updated successfully.',
            'data'    => $platform,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/platforms/{id}",
     *     summary="Delete platform",
     *     description="Remove a specific platform record.",
     *     tags={"Platforms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Platform ID", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Platform deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Platform deleted successfully.")
     *         )
     *     )
     * )
     */
    public function destroy(Platform $platform)
    {
        $platform->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Platform deleted successfully.',
        ]);
    }
}

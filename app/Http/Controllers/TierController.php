<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;

use App\Models\Tier;
use App\Services\TierService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TierController extends BaseController
{
    public function __construct(
        ApiResource $apiResource,
        private readonly TierService $tierService,
    ) {
        parent::__construct($apiResource);
    }
    /**
     * @OA\Info(
     *     title="Dukaverse API",
     *     version="1.0.0",
     *     description="API documentation for managing Tiers"
     * )
     *
     * @OA\SecurityScheme(
     *     securityScheme="bearerAuth",
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT"
     * )
     */

    /**
     * @OA\Get(
     *     path="/api/v1/admin/tiers",
     *     summary="Get all tiers",
     *     tags={"Tiers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Tiers fetched successfully")
     * )
     */
    public function index()
    {
        try {
            $result = $this->tierService->getAllTiers();
            if (isset($result['error'])) {
                return $this->sendError('Error fetching tiers', ['error' => $result['error']]);
            }

            return $this->sendResponse($result["data"], 'Tiers fetched successfully');
        } catch (Exception $ex) {
            return $this->sendError('Server Error', ['error' => $ex->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/tiers/create",
     *     summary="Get tier creation metadata",
     *     tags={"Tiers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Tier creation metadata fetched successfully")
     * )
     */
    public function create()
    {
        try {

            $result = $this->tierService->create();
            if (isset($result['error'])) {
                return $this->sendError('Error fetching tier creation metadata', ['error' => $result['error']]);
            }
            return $this->sendResponse($result["data"], 'Tiers data fetched successfully');
        } catch (Exception $ex) {
            return $this->sendError('Server Error', ['error' => $ex->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/tiers",
     *     summary="Create a new tier",
     *     tags={"Tiers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","description","type","price","benefits","billing_duration","is_active","is_recommended"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="benefits", type="string"),
     *             @OA\Property(property="billing_duration", type="string"),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="is_recommended", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Tier created successfully"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        try {

            $result = $this->tierService->store($request->all());
            if (isset($result['error'])) {
                return $this->sendError('Bad request', ['errors' => $result['error']]);
            }
            return $this->sendResponse($result["data"], 'Tier created successfully');
        } catch (Exception $ex) {
            return $this->sendError('Server Error', ['error' => $ex->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/tiers/{tier}",
     *     summary="Get a tier by ID",
     *     tags={"Tiers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="tier", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Tier fetched successfully"),
     *     @OA\Response(response=404, description="Tier not found")
     * )
     */
    public function show(string $tier)
    {
        try {

            $result = $this->tierService->show($tier);

            if(isset($result['error'])) {
                return $this->sendError('Tier not found', ['message' => 'No tier found with the given ID'], 404);
            }
            return $this->sendResponse($result["data"], 'Tier fetched successfully');
        } catch (Exception $ex) {
            return $this->sendError('Server Error', ['error' => $ex->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/tiers/{id}/edit",
     *     summary="Get a tier for editing",
     *     tags={"Tiers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Tier to edit fetched successfully")
     * )
     */
    public function edit(string $id)
    {
        try {
            $result = $this->tierService->edit($id);

            if(isset($result['error'])) {
                return $this->sendError('Tier not found', ['message' => 'No tier found with the given ID'], 404);
            }
            return $this->sendResponse($result["data"], 'Tier to edit fetched successfully');
        } catch (Exception $ex) {
            return $this->sendError('Server Error', ['error' => $ex->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/tiers/{id}",
     *     summary="Update a tier",
     *     tags={"Tiers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","description","type","price","benefits","billing_duration","is_active","is_recommended"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="benefits", type="string"),
     *             @OA\Property(property="billing_duration", type="string"),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="is_recommended", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Tier updated successfully"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function update(Request $request, string $id)
    {
        try {

            $result = $this->tierService->update($request->all(), $id);
            if (isset($result['error'])) {
                return $this->sendError('Bad request', ['errors' => $result['error']]);
            }

            $result = $this->tierService->show($id);
            if(isset($result['error'])) {
                return $this->sendError('Tier not found', ['message' => 'No tier found with the given ID'], 404);
            }
            return $this->sendResponse($result["data"], 'Tier updated successfully');
        } catch (Exception $ex) {
            return $this->sendError('Server Error', ['error' => $ex->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/tiers/{id}",
     *     summary="Delete a tier",
     *     tags={"Tiers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Tier deleted successfully"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function destroy(string $id)
    {
        try {

            $result = $this->tierService->destroy($id);
            if (isset($result['error'])) {
                return $this->sendError('Bad request', ['errors' => $result['error']]);
            }
            return $this->sendResponse($result["data"], 'Tier deleted successfully');

        } catch (Exception $ex) {
            return $this->sendError('Server Error', ['error' => $ex->getMessage()], 500);
        }
    }
}

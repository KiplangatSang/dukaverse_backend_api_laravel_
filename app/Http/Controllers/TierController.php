<?php
namespace App\Http\Controllers;

use App\Models\Tier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TierController extends BaseController
{
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
     *     path="/api/tiers",
     *     summary="Get all tiers",
     *     tags={"Tiers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Tiers fetched successfully")
     * )
     */
    public function index()
    {
        $tiers = Tier::all();
        return $this->sendResponse(['tiers' => $tiers], 'Tiers fetched successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/tiers/create",
     *     summary="Get tier creation metadata",
     *     tags={"Tiers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Tier creation metadata fetched successfully")
     * )
     */
    public function create()
    {
        $tier_types        = Tier::tier_types;
        $billing_durations = Tier::BILLINGDURATIONS;

        return $this->sendResponse([
            'tier_types'        => $tier_types,
            'billing_durations' => $billing_durations,
        ], 'Tiers data fetched successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/tiers",
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
        $validator = Validator::make($request->all(), [
            "name"             => ['required'],
            "description"      => ['required'],
            "type"             => ['required'],
            "price"            => ['required'],
            "benefits"         => ['required'],
            "billing_duration" => ['required'],
            "is_active"        => ['required'],
            "is_recommended"   => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $dukaverse = $this->dukaverse();
        $tier      = $dukaverse->tiers()->create($request->all());

        if (! $tier) {
            return $this->sendError('Bad request', [
                'errors'  => $validator->errors(),
                'message' => 'Tier could not be created',
            ]);
        }

        return $this->sendResponse(['tier' => $tier], 'Tier created successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/tiers/{id}",
     *     summary="Get a tier by ID",
     *     tags={"Tiers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Tier fetched successfully"),
     *     @OA\Response(response=404, description="Tier not found")
     * )
     */
    public function show(string $id)
    {
        $tier = Tier::where('id', $id)->first();
        return $this->sendResponse(['tier' => $tier], 'Tier fetched successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/tiers/{id}/edit",
     *     summary="Get a tier for editing",
     *     tags={"Tiers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Tier to edit fetched successfully")
     * )
     */
    public function edit(string $id)
    {
        $tier              = Tier::where('id', $id)->first();
        $tier_types        = Tier::tier_types;
        $billing_durations = Tier::BILLINGDURATIONS;

        return $this->sendResponse([
            'tier'              => $tier,
            'tier_types'        => $tier_types,
            'billing_durations' => $billing_durations,
        ], 'Tier to edit fetched successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/tiers/{id}",
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
        $validator = Validator::make($request->all(), [
            "name"             => ['required'],
            "description"      => ['required'],
            "benefits"         => ['required'],
            "type"             => ['required'],
            "price"            => ['required'],
            "billing_duration" => ['required'],
            "is_active"        => ['required'],
            "is_recommended"   => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $tier   = Tier::where('id', $id)->first();
        $result = $tier->update($request->all());

        if (! $result) {
            return $this->sendError('Bad request', [
                'errors'  => $validator->errors(),
                'message' => 'Tier could not be updated',
            ]);
        }

        $tier = Tier::where('id', $id)->first();
        return $this->sendResponse(['tier' => $tier], 'Tier updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/tiers/{id}",
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
        $result = Tier::destroy($id);

        if (! $result) {
            return $this->sendError('Bad request', ['message' => 'Tier could not be deleted']);
        }

        return $this->sendResponse([], 'Tier deleted successfully');
    }
}

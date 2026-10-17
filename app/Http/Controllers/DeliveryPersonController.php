<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Http\Requests\StoreDeliveryPersonRequest;
use App\Http\Requests\UpdateDeliveryPersonRequest;
use App\Models\DeliveryPerson;
use App\Services\DeliveryPersonService;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\Request;


class DeliveryPersonController extends BaseController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly DeliveryPersonService $deliveryPersonService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/delivery-people",
     *     summary="Get all delivery people with filtering and pagination",
     *     tags={"Delivery People"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="status", in="query", description="Filter by status", @OA\Schema(type="string", enum={"pending","active","suspended","banned","inactive"})),
     *     @OA\Parameter(name="is_online", in="query", description="Filter by online status", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="is_verified", in="query", description="Filter by verification status", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="is_accredited", in="query", description="Filter by accreditation status", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="city", in="query", description="Filter by city", @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", description="Search by name, email, or phone", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_by", in="query", description="Sort field", @OA\Schema(type="string", default="created_at")),
     *     @OA\Parameter(name="sort_direction", in="query", description="Sort direction", @OA\Schema(type="string", enum={"asc","desc"}, default="desc")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Delivery people retrieved successfully"),
     *     @OA\Response(response=500, description="Error retrieving delivery people")
     * )
     */
    public function index(Request $request)
    {
        try {
            $deliveryPeople = $this->deliveryPersonService->getIndexData($request);
            return $this->sendResponse($deliveryPeople, "Delivery people retrieved successfully");
        } catch (Exception $e) {
            return $this->sendError('Error retrieving delivery people', $e->getMessage());
        }
    }

    /**
     * Create a new delivery person
     */
    public function store(StoreDeliveryPersonRequest $request)
    {
        try {
            $result = $this->deliveryPersonService->createDeliveryPerson($request);
            return $this->sendResponse($result, "Delivery person created successfully", 201);
        } catch (Exception $e) {
            return $this->sendError('Error creating delivery person', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/delivery-people/{deliveryPerson}",
     *     summary="Get a specific delivery person's details",
     *     tags={"Delivery People"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="deliveryPerson", in="path", required=true, @OA\Schema(type="integer"), description="Delivery Person ID"),
     *     @OA\Response(response=200, description="Delivery person details retrieved successfully"),
     *     @OA\Response(response=404, description="Delivery person not found"),
     *     @OA\Response(response=500, description="Error retrieving delivery person")
     * )
     */
    public function show(DeliveryPerson $deliveryPerson)
    {
        try {
            $data = $this->deliveryPersonService->getShowData($deliveryPerson->id);
            return $this->sendResponse($data, "Delivery person details retrieved successfully");
        } catch (Exception $e) {
            return $this->sendError('Error retrieving delivery person', $e->getMessage());
        }
    }

    /**
     * Update an existing delivery person
     */
    public function update(UpdateDeliveryPersonRequest $request, DeliveryPerson $deliveryPerson)
    {
        try {
            $result = $this->deliveryPersonService->updateDeliveryPerson($deliveryPerson, $request);
            return $this->sendResponse($result, "Delivery person updated successfully");
        } catch (Exception $e) {
            return $this->sendError('Error updating delivery person', $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/delivery-people/{deliveryPerson}",
     *     summary="Delete a delivery person",
     *     tags={"Delivery People"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="deliveryPerson", in="path", required=true, @OA\Schema(type="integer"), description="Delivery Person ID"),
     *     @OA\Response(response=200, description="Delivery person deleted successfully"),
     *     @OA\Response(response=404, description="Delivery person not found"),
     *     @OA\Response(response=500, description="Error deleting delivery person")
     * )
     */
    public function destroy(DeliveryPerson $deliveryPerson)
    {
        try {
            $result = $this->deliveryPersonService->deleteDeliveryPerson($deliveryPerson);
            if (isset($result['error'])) {
                return $this->sendError($result['error'], [], $result['httpCode'] ?? 500);
            }
            return $this->sendResponse([], "Delivery person deleted successfully");
        } catch (Exception $e) {
            return $this->sendError('Server Error', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Verify a delivery person
     */
    public function verify(DeliveryPerson $deliveryPerson)
    {
        try {
            $result = $this->deliveryPersonService->verifyDeliveryPerson($deliveryPerson, $this->authService->user());
            return $this->sendResponse($result, "Delivery person verified successfully");
        } catch (Exception $e) {
            return $this->sendError('Error verifying delivery person', $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/delivery-people/{deliveryPerson}/accredit",
     *     summary="Accredit a delivery person",
     *     tags={"Delivery People"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="deliveryPerson", in="path", required=true, @OA\Schema(type="integer"), description="Delivery Person ID"),
     *     @OA\Response(response=200, description="Delivery person accredited successfully"),
     *     @OA\Response(response=404, description="Delivery person not found"),
     *     @OA\Response(response=500, description="Error accrediting delivery person")
     * )
     */
    public function accredit(DeliveryPerson $deliveryPerson)
    {
        try {
            $result = $this->deliveryPersonService->accreditDeliveryPerson($deliveryPerson, $this->authService->user());
            return $this->sendResponse($result, "Delivery person accredited successfully");
        } catch (Exception $e) {
            return $this->sendError('Error accrediting delivery person', $e->getMessage());
        }
    }

    /**
     * Update delivery person status
     */
    public function updateStatus(Request $request, DeliveryPerson $deliveryPerson)
    {
        $request->validate([
            'status' => 'required|in:pending,active,suspended,banned,inactive'
        ]);

        try {
            $result = $this->deliveryPersonService->updateStatus($deliveryPerson, $request->status);
            return $this->sendResponse($result, "Delivery person status updated successfully");
        } catch (Exception $e) {
            return $this->sendError('Error updating delivery person status', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/delivery-people/{deliveryPerson}/earnings",
     *     summary="Get delivery person's earnings",
     *     tags={"Delivery People"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="deliveryPerson", in="path", required=true, @OA\Schema(type="integer"), description="Delivery Person ID"),
     *     @OA\Parameter(name="payment_status", in="query", description="Filter by payment status", @OA\Schema(type="string", enum={"paid","pending","processing","failed"})),
     *     @OA\Parameter(name="period", in="query", description="Filter by period", @OA\Schema(type="string")),
     *     @OA\Parameter(name="start_date", in="query", description="Start date filter", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="end_date", in="query", description="End date filter", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Delivery person earnings retrieved successfully"),
     *     @OA\Response(response=404, description="Delivery person not found"),
     *     @OA\Response(response=500, description="Error retrieving earnings")
     * )
     */
    public function getEarnings(Request $request, DeliveryPerson $deliveryPerson)
    {
        try {
            $data = $this->deliveryPersonService->getEarnings($deliveryPerson, $request);
            return $this->sendResponse($data, "Delivery person earnings retrieved successfully");
        } catch (Exception $e) {
            return $this->sendError('Error retrieving earnings', $e->getMessage());
        }
    }

    /**
     * Get delivery person's deliveries
     */
    public function getDeliveries(Request $request, DeliveryPerson $deliveryPerson)
    {
        try {
            $data = $this->deliveryPersonService->getDeliveries($deliveryPerson, $request);
            return $this->sendResponse($data, "Delivery person deliveries retrieved successfully");
        } catch (Exception $e) {
            return $this->sendError('Error retrieving deliveries', $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/delivery-people/{deliveryPerson}/online",
     *     summary="Set delivery person online",
     *     tags={"Delivery People"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="deliveryPerson", in="path", required=true, @OA\Schema(type="integer"), description="Delivery Person ID"),
     *     @OA\Response(response=200, description="Delivery person is now online"),
     *     @OA\Response(response=404, description="Delivery person not found"),
     *     @OA\Response(response=500, description="Error setting delivery person online")
     * )
     */
    public function goOnline(DeliveryPerson $deliveryPerson)
    {
        try {
            $result = $this->deliveryPersonService->setOnline($deliveryPerson);
            return $this->sendResponse($result, "Delivery person is now online");
        } catch (Exception $e) {
            return $this->sendError('Error setting delivery person online', $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/delivery-people/{deliveryPerson}/offline",
     *     summary="Set delivery person offline",
     *     tags={"Delivery People"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="deliveryPerson", in="path", required=true, @OA\Schema(type="integer"), description="Delivery Person ID"),
     *     @OA\Response(response=200, description="Delivery person is now offline"),
     *     @OA\Response(response=404, description="Delivery person not found"),
     *     @OA\Response(response=500, description="Error setting delivery person offline")
     * )
     */
    public function goOffline(DeliveryPerson $deliveryPerson)
    {
        try {
            $result = $this->deliveryPersonService->setOffline($deliveryPerson);
            return $this->sendResponse($result, "Delivery person is now offline");
        } catch (Exception $e) {
            return $this->sendError('Error setting delivery person offline', $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/delivery-people/{deliveryPerson}/available",
     *     summary="Set delivery person availability",
     *     tags={"Delivery People"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="deliveryPerson", in="path", required=true, @OA\Schema(type="integer"), description="Delivery Person ID"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"available"},
     *             @OA\Property(property="available", type="boolean", description="Availability status")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Delivery person availability updated successfully"),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Delivery person not found"),
     *     @OA\Response(response=500, description="Error updating availability")
     * )
     */
    public function setAvailable(Request $request, DeliveryPerson $deliveryPerson)
    {
        $request->validate([
            'available' => 'required|boolean'
        ]);

        try {
            $result = $this->deliveryPersonService->setAvailable($deliveryPerson, $request->available);
            $status = $request->available ? 'available' : 'unavailable';
            return $this->sendResponse($result, "Delivery person is now {$status}");
        } catch (Exception $e) {
            return $this->sendError('Error updating availability', $e->getMessage());
        }
    }
}

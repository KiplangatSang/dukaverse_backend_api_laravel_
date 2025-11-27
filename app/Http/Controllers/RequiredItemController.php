<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreRequiredItemRequest;
use App\Models\RequiredItem;
use App\Repositories\RequiredItemsRepository;
use Illuminate\Http\Request;

class RequiredItemController extends BaseController
{
    private $requiredItemsRepo;
    private $retail;

    /**
     * @OA\Get(
     *     path="/api/v1/required-items",
     *     summary="Get a list of required items",
     *     description="Retrieves all required items for the authenticated user's retail store.",
     *     tags={"Required Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of required items retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product_name", type="string", example="Blue Widget"),
     *                 @OA\Property(property="sku", type="string", example="BW-001"),
     *                 @OA\Property(property="quantity_required", type="integer", example=50),
     *                 @OA\Property(property="current_stock", type="integer", example=10),
     *                 @OA\Property(property="priority", type="string", example="high"),
     *                 @OA\Property(property="needed_by", type="string", format="date", example="2025-10-01"),
     *                 @OA\Property(property="notes", type="string", example="Urgent restock before October."),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-20T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-25T08:45:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Could not retrieve items"
     *     )
     * )
     */

    public function index()
    {
        $requiredItemsData = $this->requiredItemsRepsitory()->indexData();

        if (! $requiredItemsData) {
            return $this->sendError("error", "Could not retrieve items");
        }
        return $this->sendResponse($requiredItemsData, "success");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/required-items",
     *     summary="Create a new required item",
     *     description="Creates a new required item in the authenticated user's retail store.",
     *     tags={"Required Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_name", "sku", "quantity_required"},
     *             @OA\Property(property="product_name", type="string", example="Blue Widget"),
     *             @OA\Property(property="sku", type="string", example="BW-001"),
     *             @OA\Property(property="quantity_required", type="integer", example=50),
     *             @OA\Property(property="current_stock", type="integer", example=10),
     *             @OA\Property(property="priority", type="string", example="high"),
     *             @OA\Property(property="needed_by", type="string", format="date", example="2025-10-01"),
     *             @OA\Property(property="notes", type="string", example="Urgent restock before October.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Required item created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="product_name", type="string", example="Blue Widget"),
     *             @OA\Property(property="sku", type="string", example="BW-001"),
     *             @OA\Property(property="quantity_required", type="integer", example=50),
     *             @OA\Property(property="current_stock", type="integer", example=10),
     *             @OA\Property(property="priority", type="string", example="high"),
     *             @OA\Property(property="needed_by", type="string", format="date", example="2025-10-01"),
     *             @OA\Property(property="notes", type="string", example="Urgent restock before October."),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-25T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-25T12:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */

    public function store(StoreRequiredItemRequest $request)
    {
        $result = $this->retail->requiredItems()->create(
            $request->all(),
        );
        return $this->sendResponse($result, "success");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/required-items/{id}",
     *     summary="Get a single required item",
     *     description="Retrieves a specific required item by its ID.",
     *     tags={"Required Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the required item",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Required item retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="product_name", type="string", example="Blue Widget"),
     *             @OA\Property(property="sku", type="string", example="BW-001"),
     *             @OA\Property(property="quantity_required", type="integer", example=50),
     *             @OA\Property(property="current_stock", type="integer", example=10),
     *             @OA\Property(property="priority", type="string", example="high"),
     *             @OA\Property(property="needed_by", type="string", format="date", example="2025-10-01"),
     *             @OA\Property(property="notes", type="string", example="Urgent restock before October."),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-25T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-25T12:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Required item not found"
     *     )
     * )
     */

    public function show(RequiredItem $requiredItem)
    {
        $requiredItem = $this->requiredItemsRepsitory()->showData($requiredItem->id);
        return $requiredItem;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/required-items/order",
     *     summary="Fetch selected required items",
     *     description="Returns the required items selected for ordering based on their IDs.",
     *     tags={"Required Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Selected required items fetched successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product_name", type="string", example="Blue Widget"),
     *                 @OA\Property(property="sku", type="string", example="BW-001"),
     *                 @OA\Property(property="quantity_required", type="integer", example=50),
     *                 @OA\Property(property="current_stock", type="integer", example=10),
     *                 @OA\Property(property="priority", type="string", example="high"),
     *                 @OA\Property(property="needed_by", type="string", format="date", example="2025-10-01"),
     *                 @OA\Property(property="notes", type="string", example="Urgent restock before October."),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-25T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-25T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No items selected"
     *     )
     * )
     */

    public function order(Request $request)
    {
        $requestValues = array_values($request->all());

        if (empty($requestValues)) {
            return $this->sendError('error', "You have not selected any item");
        }

        for ($i = 0; $i < count($request->all()); $i++) {
            $requireditem                     = $this->getAccount()->requiredItems()->where('id', $requestValues[$i])->with('items')->first();
            $requiredItems[$requireditem->id] = $requireditem;
        }

        return $this->sendResponse($requiredItems, "RequiredItems fetched");
    }

    public function requiredItemsRepsitory()
    {
        $this->retail            = $this->getAccount();
        $this->requiredItemsRepo = new RequiredItemsRepository($this->retail);
        return $this->requiredItemsRepo;
    }
}

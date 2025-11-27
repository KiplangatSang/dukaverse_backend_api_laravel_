<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreRetailItemRequest;
use App\Http\Requests\UpdateRetailItemRequest;
use App\Repositories\ItemRepository;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RestockImport;
use App\Models\RetailItem;

/**
 * @OA\Tag(
 *     name="Retail Items",
 *     description="Manage retail items and inventory"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class RetailItemController extends BaseController
{

    private $itemrepo;
    private $retail;

    public function itemRepository()
    {
        # code...
        $this->retail   = $this->getAccount();
        $this->itemrepo = new ItemRepository($this->retail);
        return $this->itemrepo;
    }
    /**
     * Display a listing of the resource.
     */

    /**
     * @OA\Get(
     *     path="/api/v1/retail-items",
     *     summary="Get list of retail items",
     *     tags={"Retail Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with item list"
     *     )
     * )
     */
    public function index()
    {
        //
        $items              = $this->itemRepository()->getItems();
        $stockscount        = null;
        $requiredItemscount = null;
        $salescount         = null;
        foreach ($items as $item) {
            $stockscount += count($item->stocks);
            $requiredItemscount += count($item->requiredItems);
            $salescount += count($item->sales);
        }

        $itemsData['stockscount']        = $stockscount;
        $itemsData['requiredItemscount'] = $requiredItemscount;
        $itemsData['salescount']         = $salescount;

        $itemsData['items'] = $items;

        return $this->sendResponse($itemsData, 'Success, Retail items');
    }

    /**
     * Show the form for creating a new resource.
     */

    /**
     * @OA\Get(
     *     path="/api/v1/retail-items/create",
     *     summary="Get data for creating a new retail item",
     *     tags={"Retail Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with list of items"
     *     )
     * )
     */
    public function create()
    {
        //
        $items = $this->getAccount()->items()->orderBy('created_at', 'desc')->get();
        return $this->sendResponse($items, 'Success, Retail items');
    }

    /**
     * Store a newly created resource in storage.
     */

    /**
     * @OA\Post(
     *     path="/api/v1/retail-items",
     *     summary="Store a new retail item",
     *     description="Creates a new retail item in the system.",
     *     tags={"Retail Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"brand", "code", "buying_price", "name", "selling_price"},
     *             @OA\Property(property="brand", type="string", example="BrandName"),
     *             @OA\Property(property="code", type="string", example="ITEM123"),
     *             @OA\Property(property="buying_price", type="number", format="float", example=10.50),
     *             @OA\Property(property="description", type="string", example="Item description here"),
     *             @OA\Property(property="image", type="string", example="image.png"),
     *             @OA\Property(property="name", type="string", example="Item Name"),
     *             @OA\Property(property="regulation", type="string", example="Regulation details"),
     *             @OA\Property(property="selling_price", type="number", format="float", example=15.75),
     *             @OA\Property(property="required_when_below", type="integer", example=5),
     *             @OA\Property(property="size", type="string", example="Medium")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item saved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="brand", type="string", example="BrandName"),
     *                 @OA\Property(property="code", type="string", example="ITEM123"),
     *                 @OA\Property(property="buying_price", type="number", format="float", example=10.50),
     *                 @OA\Property(property="description", type="string", example="Item description here"),
     *                 @OA\Property(property="image", type="string", example="image.png"),
     *                 @OA\Property(property="name", type="string", example="Item Name"),
     *                 @OA\Property(property="regulation", type="string", example="Regulation details"),
     *                 @OA\Property(property="selling_price", type="number", format="float", example=15.75),
     *                 @OA\Property(property="required_when_below", type="integer", example=5),
     *                 @OA\Property(property="size", type="string", example="Medium"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-25T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-25T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */

    public function store(StoreRetailItemRequest $request)
    {
        //

        $update = $request->only([
            "brand",
            "code",
            "buying_price",
            "description",
            "image",
            "name",
            "regulation",
            "selling_price",
            "required_when_below",
            "size",
        ]);

        $validated = $request->validated();

        $fileUrlsToStore = [];

        $retail_item_data = [];

        if ($request->product_images_count && $request->product_images_count > 0) {

            for ($i = 0; $i < $request->product_images_count; $i++) {
                $image_index         = 'image' . $i;
                $file                = $this->saveFile("stock_image_" . $i, $request[$image_index]);
                $fileUrlsToStore[$i] = $file;
            }
            $retail_item_data          = $request->except(['image']);
            $retail_item_data['image'] = $fileUrlsToStore[0];
        }
        if ($request->stockImageUrl) {
            $fileUrlsToStore[0] = $request->stockImageUrl;
            if (! $fileUrlsToStore[0]) {
                return $this->sendError('error', "Could not save this image");
            }
            $retail_item_data          = $request->except(['image']);
            $retail_item_data['image'] = $fileUrlsToStore[0];
        }

        $productColors = null;
        $productSizes  = null;

        if (
            $request->productSizes
        ) {
            $productSizes = $this->getProductSizes($request->productSizes);
        }

        if (
            $request->productColors
        ) {
            $productColors = $this->getProductColors($request->productColors);
        }

        $item = $this->itemRepository()->saveItems($retail_item_data, $productColors,
            $productSizes, $fileUrlsToStore);
        if (! $item) {
            return $this->sendError("Could not save item");
        }

        return $this->sendResponse($item, "success, Item saved successfully.");

    }

    private function getProductSizes($productSizes)
    {
        $productSizes = json_decode($productSizes);
        return $productSizes;
    }

    private function getProductColors($productColors)
    {
        $productColors = json_decode($productColors);
        return $productColors;
    }

    /**
     * Display the specified resource.
     */

    /**
     * @OA\Get(
     *     path="/api/v1/retail-items/{id}",
     *     summary="Get a specific retail item",
     *     tags={"Retail Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Retail Item ID"
     *     ),
     *     @OA\Response(response=200, description="Item retrieved successfully")
     * )
     */
    public function show($retailItem)
    {
        //
        $item = $this->itemRepository()->getItem($retailItem);
        if (! $item) {
            return $this->sendError("Sorry! Could not get the item");
        }

        return $this->sendResponse($item, "success");
    }

    /**
     * Show the form for editing the specified resource.
     */

    /**
     * @OA\Get(
     *     path="/api/v1/retail-items/{id}/edit",
     *     summary="Edit a specific retail item",
     *     tags={"Retail Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Retail Item ID"
     *     ),
     *     @OA\Response(response=200, description="Item fetched for editing")
     * )
     */
    public function edit($retailItem)
    {
        //
        $item = $this->getAccount()->items()
            ->where('id', $retailItem)
            ->first();
        return $this->sendResponse($item, 'success');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/retail-items/upload-image",
     *     summary="Upload image for a retail item",
     *     tags={"Retail Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="image", type="file")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Image uploaded successfully")
     * )
     */
    public function uploadImage(Request $request)
    {
        // Validate the request
        $request->validate([
            'image' => 'required|image|max:2048', // Adjust validation rules as needed
        ]);

        // Handle the file upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // Customize the file storage path and name as needed
            $path = $file->store('retail-item-images', 'public');

            // You can save the file path to the database or perform any other necessary actions here

            return response()->json(['message' => 'Image uploaded successfully']);
        } else {
            return response()->json(['error' => 'Image not found in the request'], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * @OA\Put(
     *     path="/api/v1/retail-items/{id}",
     *     summary="Update a specific retail item",
     *     tags={"Retail Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Retail Item ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="brand", type="string", example="BrandName"),
     *             @OA\Property(property="buying_price", type="number", format="float", example=10.50),
     *             @OA\Property(property="code", type="string", example="ITEM123"),
     *             @OA\Property(property="description", type="string", example="Item description here"),
     *             @OA\Property(property="image", type="string", example="image.png"),
     *             @OA\Property(property="name", type="string", example="Item Name"),
     *             @OA\Property(property="regulation", type="string", example="Regulation details"),
     *             @OA\Property(property="selling_price", type="number", format="float", example=15.75),
     *             @OA\Property(property="size", type="string", example="Medium")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="brand", type="string", example="BrandName"),
     *                 @OA\Property(property="code", type="string", example="ITEM123"),
     *                 @OA\Property(property="buying_price", type="number", format="float", example=10.50),
     *                 @OA\Property(property="description", type="string", example="Item description here"),
     *                 @OA\Property(property="image", type="string", example="image.png"),
     *                 @OA\Property(property="name", type="string", example="Item Name"),
     *                 @OA\Property(property="regulation", type="string", example="Regulation details"),
     *                 @OA\Property(property="selling_price", type="number", format="float", example=15.75),
     *                 @OA\Property(property="size", type="string", example="Medium"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-25T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-25T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item not found"
     *     )
     * )
     */

    public function update(UpdateRetailItemRequest $request, $retailItem)
    {
        $update = $request->only([
            "brand",
            "buying_price",
            "code",
            "description",
            "image",
            "name",
            "regulation",
            "selling_price",
            "size",
        ]);

        if ($request['image']) {
            $fileNameToStore = $this->saveFile("stock_image", $request['image']);

            if (! $fileNameToStore) {
                return $this->sendError('error', "Could not save this image");
            }

            $request          = $request->except(['image', '_token']);
            $request['image'] = $fileNameToStore;
        } else {
            $request = $request->except(['_token']);
        }

        $update = $this->itemRepository()
            ->updateItems($request, $retailItem);
        if (! $update) {
            return $this->sendError($retailItem, "Could not update this item");
        }

        $item = $this->getAccount()->items()
            ->where('id', $retailItem)
            ->first();

        $stock_update = $item->stocks()->update([
            "selling_price" => $item->selling_price,
        ]);

        if (! $stock_update) {
            return $this->sendError("Could not update this item's stocks", $retailItem);
        }

        return $this->sendResponse($item, 'success');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/retail-items/upload-excel",
     *     summary="Upload Excel file for restocking retail items",
     *     description="Uploads an Excel file containing retail item data and quantities for restocking. Creates new items if they don't exist and adds stock quantities.",
     *     tags={"Retail Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="file",
     *                     description="Excel file (.xlsx or .xls) with columns: name, code, brand, buying_price, selling_price, description, quantity"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Restock completed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"), example={}),
     *             @OA\Property(property="message", type="string", example="Restock completed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error - invalid file format"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function uploadExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $account = $this->getAccount();

        Excel::import(new RestockImport($account), $request->file('file'));

        return $this->sendResponse([], 'Restock completed successfully');
    }

    /**
     * Remove the specified resource from storage.
     */

    /**
     * @OA\Delete(
     *     path="/api/v1/retail-items/{id}",
     *     summary="Delete a retail item",
     *     tags={"Retail Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Retail Item ID"
     *     ),
     *     @OA\Response(response=200, description="Item deleted successfully")
     * )
     */
    public function destroy($retailItem)
    {
        //
        RetailItem::delete($retailItem);
        return $this->sendResponse([], 'Retail Item deleted successfully');

        // return redirect('/client/items/index')->with('success', "Item has been added successfully");
    }
}

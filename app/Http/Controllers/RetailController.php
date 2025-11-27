<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreRetailRequest;
use App\Http\Requests\UpdateRetailRequest;
use App\Models\Retail;
use App\Models\User;
use App\Repositories\RetailGoodsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RetailController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/retails",
     *     summary="Get all retails for the logged-in user",
     *     description="Fetches a list of all retail accounts associated with the authenticated user.",
     *     tags={"Retail"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of retails retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=12),
     *                 @OA\Property(property="retail_name_id", type="string", example="MyS123456789"),
     *                 @OA\Property(property="retail_name", type="string", example="My Shop"),
     *
     *                 @OA\Property(
     *                     property="retail_goods",
     *                     type="array",
     *                     @OA\Items(type="string", example="item1")
     *                 ),
     *
     *                 @OA\Property(property="payment_preference", type="string", example="mpesapaybill"),
     *
     *                 @OA\Property(
     *                     property="account_details",
     *                     type="object",
     *                     @OA\Property(property="paybill", type="string", example="123456"),
     *                     @OA\Property(property="account_number", type="string", example="MyAccount")
     *                 ),
     *
     *                 @OA\Property(property="retail_profile", type="string", example="/uploads/retail-profile.png"),
     *
     *                 @OA\Property(
     *                     property="retail_documents",
     *                     type="object",
     *                     @OA\Property(property="business_permit", type="string", example="/uploads/permit.pdf")
     *                 ),
     *
     *                 @OA\Property(
     *                     property="retail_relevant_documents",
     *                     type="array",
     *                     @OA\Items(type="string", example="/uploads/doc1.pdf")
     *                 ),
     *
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-25T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-25T12:00:00Z")
     *             )
     *         )
     *     )
     * )
     */

    public function index()
    {
        $retails = $this->user()->retails()->get();
        return $retails;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/retails/create",
     *     summary="Get data required for retail creation",
     *     description="Fetches available retail goods and location details for setting up a new retail.",
     *     tags={"Retail"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Retail creation data",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="retail_goods",
     *                 type="array",
     *                 @OA\Items(type="string", example="Widgets")
     *             ),
     *             @OA\Property(
     *                 property="region",
     *                 type="object",
     *                 @OA\Property(property="country", type="string", example="Kenya"),
     *                 @OA\Property(property="state", type="string", example="Nairobi"),
     *                 @OA\Property(property="city", type="string", example="Nairobi"),
     *                 @OA\Property(property="postal_code", type="string", example="00100")
     *             )
     *         )
     *     )
     * )
     */

    public function create()
    {
        $region          = $this->getLocationDetails();
        $retailGoodsRepo = new RetailGoodsRepository();
        $retailGoods     = $retailGoodsRepo->retailGoods();

        $retaildata = [
            "retail_goods" => $retailGoods,
            "region"       => $region,
        ];

        return $retaildata;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/retails/simple",
     *     summary="Create a simple retail",
     *     description="Creates a basic retail profile with a generated retail_name_id.",
     *     tags={"Retail"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"retail_name","retail_goods"},
     *             @OA\Property(property="retail_name", type="string", example="My Shop"),
     *             @OA\Property(property="retail_goods", type="array", @OA\Items(type="string", example="Widgets"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Retail created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=12),
     *             @OA\Property(property="retail_name_id", type="string", example="MyS123456789"),
     *             @OA\Property(property="retail_name", type="string", example="My Shop"),
     *             @OA\Property(
     *                 property="retail_goods",
     *                 type="array",
     *                 @OA\Items(type="string", example="Widgets")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Failed to register retail")
     * )
     */
    public function createSimpleRetail(Request $request)
    {
        $request->validate([
            "retail_name"  => "required|string|min:3",
            "retail_goods" => "required|array",
        ]);

        $retail_name_id = $this->generateRetailName($request->retail_name);
        $retailResult   = $this->user()->retails()->updateOrCreate(
            [
                'retail_name_id' => $retail_name_id,
                'user_id'        => $this->user()->id,
            ],
            [
                "retail_name"  => $request->retail_name,
                "retail_goods" => json_encode($request->retail_goods),
            ]
        );

        if (! $retailResult) {
            return $this->sendError('error', "Could not register retail");
        }

        $accountRes = $this->createAccount("Retail", $retailResult);

        if (! $accountRes) {
            return $this->sendError('error', "Could not create retail account");
        }

        return $this->sendResponse($retailResult, "Retail registered successfully");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/retails",
     *     summary="Store a retail resource",
     *     description="Creates or updates a retail resource for the authenticated user.",
     *     tags={"Retail"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"retail_name", "retail_goods"},
     *             @OA\Property(property="retail_name_id", type="string", example="MyS123456789"),
     *             @OA\Property(property="retail_name", type="string", example="My Shop"),
     *             @OA\Property(property="retail_goods", type="array", @OA\Items(type="string", example="Widgets"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Retail created or updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=12),
     *             @OA\Property(property="retail_name_id", type="string", example="MyS123456789"),
     *             @OA\Property(property="retail_name", type="string", example="My Shop"),
     *             @OA\Property(
     *                 property="retail_goods",
     *                 type="array",
     *                 @OA\Items(type="string", example="Widgets")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Could not register retail")
     * )
     */

    public function store(StoreRetailRequest $request)
    {
        $validated                 = $request->validated();
        $retail_name_id            = $request->retail_name_id ?? $this->generateRetailName($request->retail_name);
        $validated['retail_goods'] = json_encode($validated['retail_goods']);

        $user         = User::where('id', Auth::id())->first();
        $retailResult = $user->retails()->updateOrCreate(
            [
                'retail_name_id' => $retail_name_id,
                'user_id'        => $this->user()->id,
            ],
            $validated,
        );

        if (! $retailResult) {
            return $this->sendError('error', "Could not register retail");
        }

        $accountRes = $this->createAccount("Retail", $retailResult);

        if (! $accountRes) {
            return $this->sendError('error', "Could not create retail account");
        }

        return $retailResult;
    }

    /**
     * @OA\Put(
     *     path="/api/v1/retails/{id}",
     *     summary="Update a retail resource",
     *     description="Updates the specified retail resource with the provided data.",
     *     tags={"Retail"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true, description="Retail ID",
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="retail_name", type="string", example="My Updated Shop"),
     *             @OA\Property(property="retail_goods", type="array", @OA\Items(type="string", example="Updated Widget")),
     *             @OA\Property(property="paymentpreference", type="string", example="mpesapaybill"),
     *             @OA\Property(property="account_details", type="object",
     *                 @OA\Property(property="paybill", type="string", example="123456"),
     *                 @OA\Property(property="account_number", type="string", example="MyAccount")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Retail updated successfully"),
     *     @OA\Response(response=404, description="Retail not found")
     * )
     */

    public function update(UpdateRetailRequest $request, Retail $retail)
    {
        if ($request->retail_goods) {
            $retail_goods            = json_decode($retail->retail_goods);
            $goods                   = array_merge((array) $retail_goods, $request->retail_goods);
            $request['retail_goods'] = json_encode($goods);
        }

        $retail->update($request->all());
        return back()->with("success", "Profile data Updated");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/retails/payment-preference/{id}",
     *     summary="Update payment preference",
     *     description="Sets the payment preference (e.g., Mpesa Paybill or Till) for the specified retail.",
     *     tags={"Retail"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true, description="Retail ID",
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"paymentpreference"},
     *             @OA\Property(property="paymentpreference", type="string", example="mpesapaybill"),
     *             @OA\Property(property="paybill", type="string", example="123456"),
     *             @OA\Property(property="account_number", type="string", example="MyAccount"),
     *             @OA\Property(property="till_number", type="string", example="789012"),
     *             @OA\Property(property="till_store", type="string", example="My Store")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment preference updated")
     * )
     */

    public function paymentPreference($id, Request $request)
    {
        $request->validate(["paymentpreference" => "required"]);
        $account_details = [];

        if ($request->paymentpreference == "mpesapaybill") {
            $request->validate(["paybill" => "required", "account_number" => "required"]);
            $account_details["paybill"]        = $request->paybill;
            $account_details["account_number"] = $request->account_number;
        } elseif ($request->paymentpreference == "mpesatill") {
            $request->validate(["till_number" => "required", "till_store" => "required"]);
            $account_details["till_number"] = $request->till_number;
            $account_details["till_store"]  = $request->till_store;
        }

        $retail = $this->getAccount();

        $retail->update([
            "paymentpreference" => $request->paymentpreference,
            "account_details"   => json_encode($account_details),
        ]);

        return back()->with("success", "Payment preference data Updated");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/retails/profile-picture",
     *     summary="Upload retail profile picture",
     *     tags={"Retail"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile picture updated")
     * )
     */

    public function updateRetailProfile(Request $request)
    {
        $retail          = $this->getAccount();
        $fileNameToStore = $this->getBaseImages()['nofile'];
        if ($request->hasFile('file')) {
            $fileNameToStore = $this->saveFile("retail_profile", $request->file('file'));
        }

        $retail->update(["retail_profile" => $fileNameToStore]);
        return 200;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/retails/documents",
     *     summary="Upload business permit document",
     *     tags={"Retail"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Business permit uploaded successfully")
     * )
     */
    public function uploadRetailDocuments(Request $request)
    {
        $retail          = $this->getAccount();
        $fileNameToStore = $this->getBaseImages()['nofile'];
        if ($request->hasFile('file')) {
            $fileNameToStore = $this->saveFile("business_permit", $request->file('file'));
        }

        $documents                    = $retail['retail_documents'] ? (array) json_decode($retail['retail_documents']) : [];
        $documents['business_permit'] = $fileNameToStore;

        $retail->update(["retail_documents" => json_encode($documents)]);
        return 200;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/retails/relevant-documents",
     *     summary="Upload additional relevant documents",
     *     tags={"Retail"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Relevant document uploaded successfully")
     * )
     */
    public function uploadRelevantDocuments(Request $request)
    {
        $retail          = $this->getAccount();
        $fileNameToStore = $this->getBaseImages()['nofile'];

        if ($request->hasFile('file')) {
            $fileNameToStore = $this->saveFile("retail_relevant_documents", $request->file('file'));
        }

        $documents   = $retail['retail_relevant_documents'] ? (array) json_decode($retail['retail_relevant_documents']) : [];
        $documents[] = $fileNameToStore;

        $retail->update(["retail_relevant_documents" => json_encode($documents)]);
    }

    private function generateRetailName($retail_name)
    {
        return substr($retail_name, 0, 3) . rand(1000, 10000000000);
    }
}

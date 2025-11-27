<?php
namespace App\Http\Controllers;

use App\Http\Requests\UpdateOfficeRequest;
use App\Models\Office;
use App\Models\User;
use App\Repositories\GoodsandServicesRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Offices",
 *     description="Endpoints for managing offices, profiles, and documents"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class OfficeController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/offices",
     *     tags={"Offices"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get a list of user's offices",
     *     @OA\Response(
     *         response=200,
     *         description="List of offices retrieved successfully"
     *     )
     * )
     */
    public function index()
    {
        $offices = $this->user()->offices;

        if (! $offices) {
            return $this->sendError('Offices data could not be fetched', ['offices' => $offices]);
        }
        return $this->sendResponse(['offices' => $offices], 'Data fetched successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/offices/create",
     *     tags={"Offices"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get metadata required for creating an office (goods, services, industries, regions)",
     *     @OA\Response(
     *         response=200,
     *         description="Office creation metadata retrieved successfully"
     *     )
     * )
     */
    public function create()
    {
        $region          = $this->getLocationDetails();
        $officeGoods     = GoodsandServicesRepository::GOODS;
        $officesServices = GoodsandServicesRepository::SERVICES;
        $industries      = GoodsandServicesRepository::INDUSTRIES;

        $goods_and_services = array_merge($officeGoods, $officesServices);

        $office_data = [
            "goods"              => $officeGoods,
            "services"           => $officesServices,
            "industry"           => $industries,
            "region"             => $region,
            "goods_and_services" => $goods_and_services,
        ];

        if (! $office_data) {
            return $this->sendError('Offices data could not be fetched', ['result' => $office_data]);
        }
        return $this->sendResponse(['office_data' => $office_data], 'Data fetched successfully');
    }

    public function generateOffieNameID($office_name)
    {
        $office_name_id = substr($office_name, 0, 3) . rand(1000, 10000000000);
        return $office_name_id;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/offices/simple",
     *     tags={"Offices"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a simple office with minimal details",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"office_name","goods_and_services"},
     *             @OA\Property(property="office_name", type="string", example="My Office"),
     *             @OA\Property(property="goods_and_services", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Office created successfully")
     * )
     */
    public function createSimpleOffice(Request $request)
    {
        $request->validate(
            [
                "office_name"        => "required|string|min:3",
                "goods_and_services" => "required|array",
            ]
        );
        $office_name_id = $this->generateOffieNameID($request->office_name);
        $officeResult   = $this->user()->offices()->updateOrCreate(
            [
                'user_id' => $this->user()->id,
            ],
            [
                "name"               => $request->office_name,
                'name_id'            => $office_name_id,
                "goods_and_services" => json_encode($request->goods_and_services),
            ]
        );

        if (! $officeResult) {
            return $this->sendError('error', "Could not register the office");
        }

        $accountRes = $this->createAccount("Office", $officeResult);

        if (! $accountRes) {
            return $this->sendError('error', "Could not create office account");
        }

        return $this->sendResponse($officeResult, "Office registered successfully");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/offices",
     *     tags={"Offices"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create or update an office with detailed information",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","goods_and_services","industry"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="goods_and_services", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="industry", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Office created or updated successfully")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name"               => "required",
            "goods_and_services" => "required",
            "industry"           => "required",
        ]);

        if ($validator->fails()) {
            return $this->sendError('error', ["errors" => $validator->errors()]);
        }

        $validated      = $validator->validated();
        $office_name_id = $request->office_name_id;
        if (! $office_name_id) {
            $office_name_id = $this->generateOffieNameID($request->office_name);
        }

        $goods                           = json_encode($validated['goods_and_services']);
        $validated['goods_and_services'] = $goods;
        $industry                        = json_encode($validated['industry']);
        $validated['industry']           = $industry;

        $user         = User::where('id', Auth::id())->first();
        $officeResult = $user->offices()->updateOrCreate(
            [
                'officeable_id'   => $request->officeable_id ?? $user->id,
                'officeable_type' => $request->officeable_type ?? User::class,
                'name_id'         => $office_name_id,
                'user_id'         => $this->user()->id,
            ],
            $validated,
        );

        if (! $officeResult) {
            return $this->sendError('error', "Could not register the office");
        }

        $accountRes = $this->createAccount("office", $officeResult);

        if (! $accountRes) {
            return $this->sendError('error', "Could not create offuce account");
        }

        return $this->sendResponse(['result' => $officeResult], "Account created successfully");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/offices/{id}",
     *     tags={"Offices"},
     *     security={{"bearerAuth":{}}},
     *     summary="Show details for a specific office",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Office details retrieved")
     * )
     */
    public function show(Office $office)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/v1/offices/{id}",
     *     tags={"Offices"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update an existing office profile",
     *     @OA\RequestBody(
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=200, description="Office profile updated successfully")
     * )
     */
    public function update(UpdateOfficeRequest $request, Office $office)
    {
        if ($request->goods_and_services) {
            $goods_and_services            = json_decode($office->goods_and_services);
            $goods                         = array_merge((array) $goods_and_services, $request->goods_and_services);
            $request['goods_and_services'] = json_encode($goods);
        }

        $office->update($request->all());
        return back()->with("success", "Profile data Updated");
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/offices/{id}",
     *     tags={"Offices"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete an office",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Office deleted successfully")
     * )
     */
    public function destroy($office)
    {
        $officeResult = Office::destroy($office);

        if (! $officeResult) {
            return $this->sendError('The office could not be deleted', ["result" => $officeResult]);
        }

        return $this->sendResponse(['result' => $officeResult], 'Office deleted successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/offices/{id}/payment-preference",
     *     tags={"Offices"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update office payment preferences",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"paymentpreference"},
     *             @OA\Property(property="paymentpreference", type="string", enum={"mpesapaybill","mpesatill"}),
     *             @OA\Property(property="paybill", type="string"),
     *             @OA\Property(property="account_number", type="string"),
     *             @OA\Property(property="till_number", type="string"),
     *             @OA\Property(property="till_store", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment preference updated successfully")
     * )
     */
    public function paymentPreference($id, Request $request)
    {
        $request->validate([
            "paymentpreference" => "required",
        ]);
        $account_details = [];

        if ($request->paymentpreference == "mpesapaybill") {
            $request->validate([
                "paybill"        => "required",
                "account_number" => "required",
            ]);
            $account_details["paybill"]        = $request->paybill;
            $account_details["account_number"] = $request->account_number;
        } elseif ($request->paymentpreference == "mpesatill") {
            $request->validate([
                "till_number" => "required",
                "till_store"  => "required",
            ]);
            $account_details["till_number"] = $request->till_number;
            $account_details["till_store"]  = $request->till_store;
        }

        $office = $this->account();

        $office->update([
            "paymentpreference" => $request->paymentpreference,
            "account_details"   => json_encode($account_details),
        ]);

        return back()->with("success", "Payment preference data Updated");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/offices/profile/upload",
     *     tags={"Offices"},
     *     security={{"bearerAuth":{}}},
     *     summary="Upload or update office profile image",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile image uploaded successfully")
     * )
     */
    public function updateOfficeProfile(Request $request)
    {
        $office          = $this->account();
        $fileNameToStore = $this->getBaseImages()['nofile'];
        if (request()->hasFile('file')) {
            $fileNameToStore = $this->saveFile("office_profile", request()->file('file'));
        }

        $office->update([
            "office_profile" => $fileNameToStore,
        ]);

        return 200;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/offices/documents/upload",
     *     tags={"Offices"},
     *     security={{"bearerAuth":{}}},
     *     summary="Upload office business permit",
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
    public function uploadOfficeDocuments(Request $request)
    {
        $office          = $this->account();
        $fileNameToStore = $this->getBaseImages()['nofile'];
        if (request()->hasFile('file')) {
            $fileNameToStore = $this->saveFile("business_permit", request()->file('file'));
        }

        $documents = null;
        if ($office['office_documents']) {
            $documents = (array) json_decode($office['office_documents']);
        }
        $documents['business_permit'] = $fileNameToStore;

        $office->update([
            "office_documents" => json_encode($documents),
        ]);

        return 200;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/offices/documents/relevant",
     *     tags={"Offices"},
     *     security={{"bearerAuth":{}}},
     *     summary="Upload additional relevant office documents",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
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
        $office          = $this->account();
        $fileNameToStore = $this->getBaseImages()['nofile'];
        if (request()->hasFile('file')) {
            $fileNameToStore = $this->saveFile("office_relevant_documents", request()->file('file'));
        }

        $documents = null;
        if ($office['office_relevant_documents']) {
            $documents = (array) json_decode($office['office_relevant_documents']);
            array_push($documents, $fileNameToStore);
        } else {
            $documents = $fileNameToStore;
        }
        $office->update([
            "office_relevant_documents" => json_encode($documents),
        ]);
    }
}

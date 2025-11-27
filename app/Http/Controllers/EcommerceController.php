<?php
namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Models\Ecommerce;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Ecommerce",
 *     description="Endpoints for managing ecommerce sites and payment gateways"
 * )
 */

class EcommerceController extends BaseController
{
    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ecommerce",
     *     tags={"Ecommerce"},
     *     summary="Get ecommerce data for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Ecommerce data retrieved"),
     *     @OA\Response(response=403, description="User does not have an ecommerce site")
     * )
     */
    public function ecommerceData()
    {
        $result = $this->user()->ecommerce;
        if (! $result) {
            return $this->sendError('You do not have an ecommerce site', ["result" => $result], 403);
        }

        $ecommerce = $this->user()->ecommerce()->first();

        return $this->sendResponse(["result" => $result, "ecommerce" => $ecommerce], 'Update successful');

    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/validate",
     *     tags={"Ecommerce"},
     *     summary="Validate user request before creating ecommerce",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"terms_and_conditions"},
     *             @OA\Property(property="terms_and_conditions", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Validation successful"),
     *     @OA\Response(response=400, description="Validation failed")
     * )
     */
    public function validateUserRequest(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                "terms_and_conditions" => 'required',
            ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["errors" => $validator->errors()]);
        }

        $can_user_create_ecommerce = $this->checkIfUserCanCreateEcommerce();
        if (! $can_user_create_ecommerce['can_create_ecommerce']) {
            return $this->sendError('You are not allowed to create an ecommerce site', ["result" => $can_user_create_ecommerce], 403);
        }
        return $this->sendResponse(["result" => $can_user_create_ecommerce], 'Update successful');

    }

    /**
     * @OA\Get(
     *     path="/api/v1/ecommerce/payment-gateways/create-data",
     *     tags={"PaymentGateways"},
     *     summary="Get available payment gateway data",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Available gateways fetched successfully")
     * )
     */

    public function getCreatePaymentGatewaysData()
    {

        $gateways = PaymentGateway::PRODUCTION_ECOMMERCE_GATEWAYS;

        return $this->sendResponse(['gateways' => $gateways], 'Success, Payment gateways fetched successfully.');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ecommerce/payment-gateways",
     *     tags={"PaymentGateways"},
     *     summary="Get all payment gateways for the ecommerce account",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Gateways fetched successfully")
     * )
     */
    public function getPaymentGateways()
    {

        $gateways = PaymentGateway::PRODUCTION_ECOMMERCE_GATEWAYS;

        $account = $this->user()->ecommerce;

        $account_gateways = $account->paymentGateways;
        // if ($account_gateways) {
        //     $account_gateways = $account_gateways->map(function ($gateway) {
        //         return [
        //             'type'        => $gateway->meta_data ? ['type'] : null,
        //             'description' => $gateway->description,
        //             'regulation'  => $gateway->regulation,
        //         ];
        //     });
        // }
        // $gateways = array_map(function ($gateway) use ($account_gateways) {
        //     $gateway['is_active'] = false;
        //     foreach ($account_gateways as $account_gateway) {
        //         $account_gateway = collect($account_gateway);
        //         if ($gateway['type'] == $account_gateway->type) {
        //             $gateway['is_active'] = true;
        //         }
        //     }
        //     return $gateway;
        // }, $gateways);

        $gateways = array_map(function ($gateway) use ($account_gateways) {
            foreach ($account_gateways as $account_gateway) {
                $account_gateway = collect($account_gateway);

            }
            return $gateway;
        }, $gateways);

        // $gateways = array_values($gateways);

        return $this->sendResponse(['gateways' => $account_gateways], 'Success, Payment gateways fetched successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/payment-gateways",
     *     tags={"PaymentGateways"},
     *     summary="Save new payment gateways",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"gateways"},
     *             @OA\Property(
     *                 property="gateways",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="type", type="string", example="MPesa")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Gateways saved successfully"),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */

    public function savePaymentGateways(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                'gateways' => ['required', 'string'],
            ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ["errors" => $validator->errors()]);
        }

        $payment_gateways       = json_decode($request->gateways);
        $mpesa_result           = null;
        $pay_on_delivery_result = null;

        // $account = $this->ecommerce($request);

        $account = $this->user()->ecommerce;

        foreach ($payment_gateways as $gateway_request) {
            if ($gateway_request->type == "MPesa") {

                //should either be till or paybill

                $validator = Validator::make(["MPesa" => $gateway_request],
                    [
                        'type' => ['sometimes', 'string'],
                    ]);

                if ($request->mpesa_account_type == "till") {
                    $validator = Validator::make($request->all(),
                        [
                            'till_number'   => ['required', "integer"],
                            'store__number' => ['required', "integer"],
                        ]);
                } else if ($request->mpesa_account_type == "paybill") {
                    $validator = Validator::make($request->all(),
                        [
                            'paybill_number' => ['sometimes', "integer"],
                        ]);
                }

                $mpesa_result = $this->saveMPesaPaymentGateway($account, $request);

            }
            if ($gateway_request->type == "pay_on_delivery") {

                $pay_on_delivery_result = $this->savePayOnDeliveryGateway($account, $request);

            }
        }

        $gateways = $account->paymentGateways;

        return $this->sendResponse(['gateways' => $gateways, 'errors' => [
            'pay_on_delivery_result' => [
                'result' => $pay_on_delivery_result,
                'error'  => 'Could not save the pay on delivery method',
            ],
            'mpesa_result'           => [
                'result' => $mpesa_result,
                'error'  => 'Could not save the mpesa method',
            ],

        ],
        ], 'Success, Payment gateways fetched successfully.');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ecommerce/payment-gateways/{id}",
     *     tags={"PaymentGateways"},
     *     summary="Get a single payment gateway by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Gateway fetched successfully"),
     *     @OA\Response(response=403, description="Gateway not found")
     * )
     */

    public function getPaymentGateway($gateway_id)
    {

        $account = $this->user()->ecommerce;

        $account_gateways = $account->paymentGateways()->where('id', $gateway_id)->first();

        if (! $account_gateways) {
            return $this->sendError('Payment gateway not found', ["result" => $account_gateways], 403);
        }

        return $this->sendResponse(['gateway' => $account_gateways], 'Success, Payment gateways fetched successfully.');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ecommerce/payment-gateways/{id}/edit",
     *     tags={"PaymentGateways"},
     *     summary="Get a payment gateway for editing",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Gateway data fetched"),
     *     @OA\Response(response=403, description="Gateway not found")
     * )
     */
    public function editPaymentGateways($gateway_id)
    {
        $account = $this->user()->ecommerce;

        $account_gateway = $account->paymentGateways()->where('id', $gateway_id)->first();

        if (! $account_gateway) {
            return $this->sendError('Payment gateway not found', ["result" => $account_gateway], 403);
        }

        $available_gateways = PaymentGateway::PRODUCTION_ECOMMERCE_GATEWAYS;

        return $this->sendResponse(['gateway' => $account_gateway, 'available_gateways' => $available_gateways], 'Success, Payment gateways fetched successfully.');

    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/payment-gateways",
     *     tags={"PaymentGateways"},
     *     summary="Save new payment gateways",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"gateways"},
     *             @OA\Property(
     *                 property="gateways",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="type", type="string", example="MPesa")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Gateways saved successfully"),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */

    // public function updatePaymentGateways($gateway_id, Request $request)
    // {
    //     $account = $this->user()->ecommerce;

    //     $account_gateway = $account->paymentGateways()->where('id', $gateway_id)->first();

    //     if (! $account_gateway) {
    //         return $this->sendError('Payment gateway not found', ["result" => $account_gateway], 403);
    //     }

    //     $validator = Validator::make($request->all(),
    //         [
    //             'gateways' => ['required', 'string'],
    //         ]);
    //     if ($validator->fails()) {
    //         return $this->sendError('Bad request', ["errors" => $validator->errors()]);
    //     }
    //     $payment_gateways       = json_decode($request->gateways);
    //     $mpesa_result           = null;
    //     $pay_on_delivery_result = null;
    //     $account                = $this->ecommerce($request);
    //     foreach ($payment_gateways as $gateway_request) {
    //         if ($gateway_request->type == "MPesa") {
    //             //should either be till or paybill
    //             $validator = Validator::make($request->all(),
    //                 [
    //                     'type' => ['sometimes', 'string'],
    //                 ]);
    //             if ($request->mpesa_account_type == "till") {
    //                 $validator = Validator::make($request->all(),
    //                     [
    //                         'till_number'   => ['required', "integer"],
    //                         'store__number' => ['required', "integer"],
    //                     ]);
    //             } else if ($request->mpesa_account_type == "paybill") {
    //                 $validator = Validator::make($request->all(),
    //                     [
    //                         'paybill_number' => ['sometimes', "integer"],
    //                     ]);
    //             }
    //             $mpesa_result = $this->updateMPesaPaymentGateway($account, $account_gateway, $request);

    //             if ($mpesa_result) {
    //                 return $this->sendError('Bad request', ["errors" => $validator->errors()]);
    //             }

    //         }
    //         if ($gateway_request->type == "pay_on_delivery") {
    //             $pay_on_delivery_result = $this->updatePayOnDeliveryGateway($account, $account_gateway, $request);

    //             if ($pay_on_delivery_result) {
    //                 return $this->sendError('Bad request', ["errors" => $validator->errors()]);
    //             }

    //         }
    //     }
    //     $account_gateway = $account->paymentGateways()->where('id', $gateway_id)->first();

    //     return $this->sendResponse(['account_gateway' => $account_gateway, 'errors' => [
    //         'pay_on_delivery_result' => [
    //             'result' => $pay_on_delivery_result,
    //             'error'  => 'Could not save the pay on delivery method',
    //         ],
    //         'mpesa_result'           => [
    //             'result' => $mpesa_result,
    //             'error'  => 'Could not save the mpesa method',
    //         ],
    //     ],
    //     ], 'Success, Payment gateways fetched successfully.');
    //     $account          = $this->user()->ecommerce;
    //     $account_gateways = $account->paymentGateways()->where('id', $gateway_id)->first();
    //     if (! $account_gateways) {
    //         return $this->sendError('Payment gateway not found', ["result" => $account_gateways], 403);
    //     }
    //     return $this->sendResponse(['gateways' => $account_gateways], 'Success, Payment gateways fetched successfully.');
    // }

    /**
     * @OA\Delete(
     *     path="/api/v1/ecommerce/payment-gateways/{id}",
     *     tags={"PaymentGateways"},
     *     summary="Delete a payment gateway",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Gateway deleted successfully"),
     *     @OA\Response(response=403, description="Gateway not found")
     * )
     */
    public function deletePaymentGateways($gateway_id)
    {

        $account = $this->user()->ecommerce;

        $account_gateway = $account->paymentGateways()->destroy($gateway_id)->first();

        if (! $account_gateway) {
            return $this->sendError('Payment gateway not found', ["result" => $account_gateway], 403);
        }

        $result = $account_gateway->delete();
        if (! $result) {
            return $this->sendError('Payment gateway not deleted', ["result" => $result], 403);
        }

        return $this->sendResponse(['result' => $result], 'Success, Payment gateways deleted successfully.');

    }

    public function saveMPesaPaymentGateway($account, $mpesa)
    {
        $ecommerce = $account;

        $mpesa_meta = null;
        if ($mpesa->mpesa_account_type == 'Till') {
            $mpesa_meta = [
                'type'         => "till",
                'till'         => $mpesa->till_number,
                'store_number' => $mpesa->store_number,
            ];
        } else if ($mpesa->mpesa_account_type == 'Paybill') {
            $mpesa_meta = [
                'type'    => "paybill",
                'paybill' => $mpesa->paybill_number,
            ];
        }

        $result = $ecommerce->paymentGateways()->create(
            [
                "gatewayable_id"   => $ecommerce->id,
                "gatewayable_type" => Ecommerce::class,
                "user_id"          => Auth::id(),
                "name"             => "mpesa",
                "description"      => $mpesa->description ?? "Pay using MPesa.",
                "meta_data"        => $mpesa_meta,
                "regulation"       => $mpesa->regulation ? [$mpesa->regulation] : null,
                "is_active"        => false,
            ]
        );

        return $result;

    }

    public function updateMPesaPaymentGateway($account, $gateway, $mpesa)
    {
        $ecommerce = $account;

        $mpesa_meta = null;
        if ($mpesa->mpesa_account_type == 'Till') {
            $mpesa_meta = [
                'type'         => "till",
                'till'         => $mpesa->till_number,
                'store_number' => $mpesa->store_number,
            ];
        } else if ($mpesa->mpesa_account_type == 'Paybill') {
            $mpesa_meta = [
                'type'    => "paybill",
                'paybill' => $mpesa->paybill_number,
            ];
        }

        $result = $gateway->update(
            [
                "gatewayable_id"   => $ecommerce->id,
                "gatewayable_type" => Ecommerce::class,
                "user_id"          => Auth::id(),
                "name"             => "mpesa",
                "description"      => $mpesa->description ?? "Pay using MPesa.",
                "meta_data"        => $mpesa_meta,
                "regulation"       => $mpesa->regulation ? [$mpesa->regulation] : null,
                "is_active"        => false,
            ]
        );

        return $result;

    }

    public function savePayOnDeliveryGateway($account, $payOnDelivery)
    {
        $ecommerce = $account;

        $payOnDelivery_meta = null;
        if ($payOnDelivery->type == 'pay_on_delivery') {
            $payOnDelivery_meta = [
                'type' => "pay_on_delivery",
            ];
        }

        $result = $ecommerce->paymentGateways()->create(
            [
                "gatewayable_id"   => $ecommerce->id,
                "gatewayable_type" => Ecommerce::class,
                "user_id"          => Auth::id(),
                "name"             => "Pay on delivery",
                "description"      => $payOnDelivery->description ?? "You will pay on delivery.",
                "meta_data"        => $payOnDelivery_meta,
                "regulation"       => $payOnDelivery->regulation ? [$payOnDelivery->regulation] : null,
                "is_active"        => false,
            ]
        );

        return $result;

    }

    public function updatePayOnDeliveryGateway($account, $gateway, $payOnDelivery)
    {
        $ecommerce = $account;

        $payOnDelivery_meta = null;
        if ($payOnDelivery->type == 'pay_on_delivery') {
            $payOnDelivery_meta = [
                'type' => "pay_on_delivery",
            ];
        }

        $result = $gateway->create(
            [
                "gatewayable_id"   => $ecommerce->id,
                "gatewayable_type" => Ecommerce::class,
                "user_id"          => Auth::id(),
                "name"             => "Pay on delivery",
                "description"      => $payOnDelivery->description ?? "You will pay on delivery.",
                "meta_data"        => $payOnDelivery_meta,
                "regulation"       => $payOnDelivery->regulation ? [$payOnDelivery->regulation] : null,
                "is_active"        => false,
            ]
        );

        return $result;

    }

    // public function registerEcommerceShop(Request $request)
    // {

    //     $validator = Validator::make($request->all(),
    //         [
    //             "terms_and_conditions" => 'required',
    //         ]);

    //     if ($validator->fails()) {
    //         return $this->sendError("Bad request", ["errors" => $validator->errors()]);
    //     }

    //     $can_user_create_ecommerce = $this->checkIfUserCanCreateEcommerce();
    //     if (!$can_user_create_ecommerce['can_create_ecommerce']) {
    //         return $this->sendError('You are not allowed to create an ecommerce site', ["result" => $can_user_create_ecommerce], 403);
    //     }
    //     return $this->sendResponse(["result" => $can_user_create_ecommerce], 'Update successful');

    // }

    private function checkIfUserCanCreateEcommerce()
    {
        $can_create_ecommerce = [];
        $user                 = $this->user();

        if (! $user->email_verified_at) {
            $can_create_ecommerce['can_create_ecommerce'] = false;
            $can_create_ecommerce['error']                = 'You are not a verified user';
            return $can_create_ecommerce;
        }

        if ($user->role == User::RETAILER_ACCOUNT_TYPE) {
            $can_create_ecommerce['can_create_ecommerce'] = true;
            $can_create_ecommerce['success']              = 'You can create your ecommerce site';
            return $can_create_ecommerce;
        }

        if ($user->role == User::DUKAVERSE_ADMIN_ACCOUNT_TYPE) {
            $can_create_ecommerce['can_create_ecommerce'] = true;
            $can_create_ecommerce['success']              = 'You can create your ecommerce site';
            return $can_create_ecommerce;
        } else {
            $can_create_ecommerce['can_create_ecommerce'] = false;
            $can_create_ecommerce['error']                = 'You are not a allowed to create the E-commerce site';
            return $can_create_ecommerce;

        }

        $can_create_ecommerce['can_create_ecommerce'] = false;
        $can_create_ecommerce['error']                = 'You are not a allowed to create the E-commerce site';
        return $can_create_ecommerce;

    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/allow",
     *     tags={"Ecommerce"},
     *     summary="Activate the ecommerce site",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Ecommerce site activated"),
     *     @OA\Response(response=400, description="Activation failed")
     * )
     */

    public function allowEcommerceSite()
    {

        $result = $this->user()->ecommerce()->update([
            'is_active' => true,
        ]);

        if (! $result) {
            return $this->sendError('Could not make the update', ["result" => $result]);
        }

        $ecommerce = $this->user()->ecommerce()->first();

        return $this->sendResponse(["result" => $result, "ecommerce" => $ecommerce], 'Update successful');

    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/register",
     *     tags={"Ecommerce"},
     *     summary="Register a new ecommerce shop",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation","phone_1","phone_2","address","country","city","town","road_or_street","building","postal_code"},
     *             @OA\Property(property="name", type="string", example="My Shop"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password"),
     *             @OA\Property(property="phone_1", type="string", example="+254700000000"),
     *             @OA\Property(property="phone_2", type="string", example="+254700000001"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="country", type="string"),
     *             @OA\Property(property="city", type="string"),
     *             @OA\Property(property="town", type="string"),
     *             @OA\Property(property="road_or_street", type="string"),
     *             @OA\Property(property="building", type="string"),
     *             @OA\Property(property="postal_code", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Ecommerce site created successfully"),
     *     @OA\Response(response=500, description="Validation error")
     * )
     */

    public function registerEcommerceShop(Request $request)
    {
        $user = $this->user();

        // if (! $user->is_retailer) {
        //     return $this->sendError("Bad request", ["You are not allowed to make this change"]);
        // }

        $validator = Validator::make($request->all(),
            [
                "name"           => 'required',
                "email"          => ['required', 'unique:ecommerces', 'email'],
                "password"       => ['required', 'string', 'min:8', 'confirmed'],
                "phone_1"        => ['required', 'unique:ecommerces'],
                "phone_2"        => ['required', 'unique:ecommerces'],
                "address"        => 'required',
                'country'        => 'required',
                'city'           => 'required',
                'town'           => 'required',
                'road_or_street' => 'required',
                'building'       => 'required',
                'postal_code'    => 'required',
                'address'        => 'required',
            ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["errors" => $validator->errors()], 500);
        }

        $generated_vendor_data = $this->generateEcommerceVendorDetails();

        $ecommerce = $user->ecommerces()->create(
            ["name"          => $request->name,
                "site_name"      => $request->site_name,
                "email"          => $request->email,
                "password"       => $request->password,
                "phone_1"        => $request->phone_1,
                "phone_2"        => $request->phone_2,
                "address"        => $request->address,
                "vendor_id"      => $generated_vendor_data['vendor_id'],
                "is_active"      => $request->is_active ?? false,
                "is_subscribed"  => $request->is_subscribed ?? false,
                'country'        => $request->country,
                'city'           => $request->city,
                'town'           => $request->town,
                'road_or_street' => $request->road_or_street,
                'building'       => $request->building,
                'postal_code'    => $request->postal_code,
                'address'        => $request->address,
                'user_id'        => Auth::id(),

            ]
        );

        return $this->sendResponse(["ecommerce" => $ecommerce], "Site created successfully");

    }

    public function generateEcommerceVendorDetails()
    {

        $vendor_id = null;
        do {
            // Generate a token
            $vendor_id = Str::random(Ecommerce::VENDOR_ID_LENGTH);

            // Validate the token's uniqueness
            $validator = Validator::make(['vendor_id' => $vendor_id], [
                'vendor_id' => 'required|unique:ecommerces,vendor_id',
            ]);

        } while ($validator->fails());

        $vendor_id   = 'DV' . $vendor_id . 'DV';
        $vendor_data = [
            "vendor_id" => $vendor_id,
        ];
        return $vendor_data;
    }

    public function applyForEcommerceSite()
    {

    }

    /**
     * @OA\Delete(
     *     path="/api/v1/ecommerce",
     *     tags={"Ecommerce"},
     *     summary="Delete the ecommerce site",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Ecommerce deleted successfully"),
     *     @OA\Response(response=400, description="Ecommerce not found")
     * )
     */

    public function destroyEcommerceSite()
    {
        $ecommerce = $this->user()->ecommerce;

        if (! $ecommerce) {
            return $this->sendError('failed request', ["error" => "ecommerce not found"]);
        }

        Ecommerce::destroy($ecommerce->id);

        return $this->sendResponse(['success' => "Request successful"], "Ecommerce removed");

    }

}

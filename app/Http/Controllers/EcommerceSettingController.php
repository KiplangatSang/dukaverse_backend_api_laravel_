<?php
namespace App\Http\Controllers;

use App\Models\Ecommerce;
use App\Models\EcommerceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Ecommerce Settings",
 *     description="API Endpoints for managing ecommerce settings, colors, sections, and preferences."
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class EcommerceSettingController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/settings/save",
     *     tags={"Ecommerce Settings"},
     *     security={{"bearerAuth":{}}},
     *     summary="Save ecommerce settings",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"allow_discounts","allow_payments","is_age_restricted","connect_all_retails","show_all_products","remove_products_in_low_stock"},
     *             @OA\Property(property="allow_discounts", type="boolean"),
     *             @OA\Property(property="allow_payments", type="boolean"),
     *             @OA\Property(property="is_age_restricted", type="boolean"),
     *             @OA\Property(property="connect_all_retails", type="boolean"),
     *             @OA\Property(property="show_all_products", type="boolean"),
     *             @OA\Property(property="show_support_contact", type="boolean"),
     *             @OA\Property(property="remove_products_in_low_stock", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Settings updated successfully"),
     *     @OA\Response(response=400, description="Validation failed")
     * )
     */
    public function saveEcommerceSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "allow_discounts"              => ["required"],
            "allow_payments"               => ["required"],
            "is_age_restricted"            => ["required"],
            "connect_all_retails"          => ["required"],
            "show_all_products"            => ["required"],
            "show_support_contact"         => ["sometimes"],
            "remove_products_in_low_stock" => ["required"],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad Request', ['errors' => $validator->errors()]);
        }

        $ecommerce = $this->user()->ecommerce;

        $result = $ecommerce->ecommerceSetting()->updateOrCreate(
            ['ecommerce_id' => $ecommerce->id, 'user_id' => Auth::id()],
            [
                "allow_discounts"              => $request->allow_discounts,
                "allow_payments"               => $request->allow_payments,
                "is_age_restricted"            => $request->is_age_restricted,
                "connect_all_retails"          => $request->connect_all_retails,
                "show_all_products"            => $request->show_all_products,
                "show_support_contact"         => $request->show_support_contact ? true : false,
                "support_contact"              => $request->show_support_contact,
                "remove_products_in_low_stock" => $request->remove_products_in_low_stock,
            ]
        );

        if (! $result) {
            return $this->sendError('Failed to save ecommerce settings', ["result" => $result]);
        }

        $ecommerce = Ecommerce::where('id', $result->id)->with('ecommerceSetting')->first();

        return $this->sendResponse(['ecommerce' => $ecommerce], 'Settings updated successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/settings/colors",
     *     tags={"Ecommerce Settings"},
     *     security={{"bearerAuth":{}}},
     *     summary="Set ecommerce colors",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"section","color"},
     *             @OA\Property(property="section", type="string"),
     *             @OA\Property(property="color", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Colors updated successfully")
     * )
     */
    public function setEcommerceColors(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'section' => ['required', 'string'],
            'color'   => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $user              = $this->user();
        $ecommerceSettings = $user->ecommerceSettings;
        $colors            = $ecommerceSettings->ecommerce_colors ?? [];

        $colors[$request->section] = $request->color;

        $result = $user->ecommerceSettings()->update(['ecommerce_colors' => $colors]);

        if (! $result) {
            return $this->sendError('Request failed', ['error' => "Could not update the colors"]);
        }
        return $this->sendResponse(['result' => $result, 'message' => "Colors updated successfully"], 'Request successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/settings/sections",
     *     tags={"Ecommerce Settings"},
     *     security={{"bearerAuth":{}}},
     *     summary="Add a section to ecommerce page",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"section","color","name","content"},
     *             @OA\Property(property="section", type="string"),
     *             @OA\Property(property="color", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="content", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Section added successfully")
     * )
     */
    public function setEcommerceSections(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'section' => ['required', 'string'],
            'color'   => ['required', 'string'],
            'name'    => ['required', 'string'],
            'content' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $file_url = null;
        if ($request->file) {
            $folder_name = 'ecommerce_sections/' . $request->section . "/" . $request->name;
            $file_url    = $this->saveFile($folder_name, $request->file);
        }

        $user               = $this->user();
        $ecommerceSettings  = $user->ecommerceSettings;
        $ecommerce_sections = $ecommerceSettings->ecommerce_sections ?? [];

        $ecommerce_sections[] = [
            'section' => $request->section,
            'color'   => $request->color,
            'name'    => $request->name,
            'content' => $request->content,
            'file'    => $file_url,
        ];

        $result = $user->ecommerceSettings()->update(['ecommerce_sections' => $ecommerce_sections]);

        if (! $result) {
            return $this->sendError('Request failed', ['error' => "Could not update the sections"]);
        }
        return $this->sendResponse(['result' => $result, 'message' => "Section added successfully"], 'Request successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/settings/sections/move-up",
     *     tags={"Ecommerce Settings"},
     *     security={{"bearerAuth":{}}},
     *     summary="Move section up in order",
     *     @OA\RequestBody(
     *         @OA\JsonContent(@OA\Property(property="section_id", type="integer"))
     *     ),
     *     @OA\Response(response=200, description="Section moved successfully")
     * )
     */
    public function moveEcommerceSectionUp(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'section_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $user               = $this->user();
        $ecommerce_sections = $user->ecommerceSettings->ecommerce_sections ?? [];

        if ($request->section_id <= 0 || $request->section_id >= count($ecommerce_sections)) {
            return $this->sendError('Bad request', ['error' => "Invalid section id"]);
        }

        [$ecommerce_sections[$request->section_id - 1], $ecommerce_sections[$request->section_id]] =
            [$ecommerce_sections[$request->section_id], $ecommerce_sections[$request->section_id - 1]];

        $result = $user->ecommerceSettings()->update(['ecommerce_sections' => $ecommerce_sections]);

        return $this->sendResponse(['result' => $result, 'message' => "Section moved up successfully"], 'Request successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/settings/sections/move-down",
     *     tags={"Ecommerce Settings"},
     *     security={{"bearerAuth":{}}},
     *     summary="Move section down in order",
     *     @OA\RequestBody(
     *         @OA\JsonContent(@OA\Property(property="section_id", type="integer"))
     *     ),
     *     @OA\Response(response=200, description="Section moved successfully")
     * )
     */
    public function moveEcommerceSectionDown(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'section_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $user               = $this->user();
        $ecommerce_sections = $user->ecommerceSettings->ecommerce_sections ?? [];

        if ($request->section_id < 0 || $request->section_id >= count($ecommerce_sections) - 1) {
            return $this->sendError('Bad request', ['error' => "Invalid section id"]);
        }

        [$ecommerce_sections[$request->section_id + 1], $ecommerce_sections[$request->section_id]] =
            [$ecommerce_sections[$request->section_id], $ecommerce_sections[$request->section_id + 1]];

        $result = $user->ecommerceSettings()->update(['ecommerce_sections' => $ecommerce_sections]);

        return $this->sendResponse(['result' => $result, 'message' => "Section moved down successfully"], 'Request successful');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/ecommerce/settings/sections",
     *     tags={"Ecommerce Settings"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete a section",
     *     @OA\RequestBody(
     *         @OA\JsonContent(@OA\Property(property="section_id", type="integer"))
     *     ),
     *     @OA\Response(response=200, description="Section deleted successfully")
     * )
     */
    public function deleteEcommerceSection(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'section_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $user               = $this->user();
        $ecommerce_sections = $user->ecommerceSettings->ecommerce_sections ?? [];

        if (! isset($ecommerce_sections[$request->section_id])) {
            return $this->sendError('Bad request', ['error' => "Invalid section id"]);
        }

        array_splice($ecommerce_sections, $request->section_id, 1);

        $result = $user->ecommerceSettings()->update(['ecommerce_sections' => $ecommerce_sections]);

        return $this->sendResponse(['result' => $result, 'message' => "Section deleted successfully"], 'Request successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/settings/allow-discounts",
     *     tags={"Ecommerce Settings"},
     *     security={{"bearerAuth":{}}},
     *     summary="Toggle discounts on ecommerce site",
     *     @OA\RequestBody(@OA\JsonContent(@OA\Property(property="allow_discounts", type="boolean"))),
     *     @OA\Response(response=200, description="Discount setting updated")
     * )
     */
    public function allowEcommerceDiscounts(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'allow_discounts' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $result = $this->user()->ecommerceSettings()->update(['allow_discounts' => $request->allow_discounts]);

        return $this->sendResponse(['result' => $result, 'message' => "Discount setting updated"], 'Request successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/settings/support-contact",
     *     tags={"Ecommerce Settings"},
     *     security={{"bearerAuth":{}}},
     *     summary="Show or hide support contact",
     *     @OA\RequestBody(@OA\JsonContent(@OA\Property(property="show_support_contact", type="boolean"))),
     *     @OA\Response(response=200, description="Support contact setting updated")
     * )
     */
    public function showSupportContactSettings(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'show_support_contact' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $result = $this->user()->ecommerceSettings()->update(['show_support_contact' => $request->show_support_contact]);

        return $this->sendResponse(['result' => $result, 'message' => "Support contact setting updated"], 'Request successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/settings/allow-payments",
     *     tags={"Ecommerce Settings"},
     *     security={{"bearerAuth":{}}},
     *     summary="Enable or disable ecommerce payments",
     *     @OA\RequestBody(@OA\JsonContent(@OA\Property(property="allow_payments", type="boolean"))),
     *     @OA\Response(response=200, description="Payment setting updated")
     * )
     */
    public function allowEcommerceSitePayments(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'allow_payments' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $result = $this->user()->ecommerceSettings()->update(['allow_payments' => $request->allow_payments]);

        return $this->sendResponse(['result' => $result, 'message' => "Payments setting updated"], 'Request successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/settings/age-restriction",
     *     tags={"Ecommerce Settings"},
     *     security={{"bearerAuth":{}}},
     *     summary="Set age restriction",
     *     @OA\RequestBody(@OA\JsonContent(@OA\Property(property="is_age_restricted", type="boolean"))),
     *     @OA\Response(response=200, description="Age restriction setting updated")
     * )
     */
    public function setAgeRestrictions(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'is_age_restricted' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $result = $this->user()->ecommerceSettings()->update(['is_age_restricted' => $request->is_age_restricted]);

        return $this->sendResponse(['result' => $result, 'message' => "Age restriction updated"], 'Request successful');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ecommerce/settings/notification-channels",
     *     tags={"Ecommerce Settings"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get notification channels",
     *     @OA\Response(response=200, description="Notification channels fetched")
     * )
     */
    public function notificationChannels()
    {
        // Implement channel retrieval here if needed
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ecommerce/settings/get-notified-when",
     *     tags={"Ecommerce Settings"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get events that trigger notifications",
     *     @OA\Response(response=200, description="Triggers fetched")
     * )
     */
    public function getNotifiedWhen()
    {
        // Implement notification trigger retrieval here
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecommerce/settings/collect-customer-info",
     *     tags={"Ecommerce Settings"},
     *     security={{"bearerAuth":{}}},
     *     summary="Enable customer info collection",
     *     @OA\Response(response=200, description="Customer info collection enabled")
     * )
     */
    public function collectCustomerInformation()
    {
        // Implement info collection toggle here

    }
}

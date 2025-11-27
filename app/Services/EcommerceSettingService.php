<?php
namespace App\Services;

use App\Http\Controllers\BaseController;
use App\Models\Ecommerce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EcommerceSettingService extends BaseService
{

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
            return $this->responseHelper->error('Bad Request', ['errors' => $validator->errors()], 400);
        }

        $ecommerce = $this->user()->ecommerce;

        $result = $ecommerce->ecommerceSetting()->updateOrCreate([
            'ecommerce_id' => $ecommerce->id,
            'user_id'      => Auth::id(),
        ],
            ["allow_discounts"             => $request->allow_discounts,
                "allow_payments"               => $request->allow_payments,
                "is_age_restricted"            => $request->is_age_restricted,
                "connect_all_retails"          => $request->connect_all_retails,
                "show_all_products"            => $request->show_all_products,
                "show_support_contact"         => $request->show_support_contact ? true : false,
                "support_contact"              => $request->show_support_contact,
                "remove_products_in_low_stock" => $request->remove_products_in_low_stock]
        );

        if (! $result) {
            return $this->responseHelper->error('Failed to save ecommerce settings', ["result" => $result], 500);

        }

        $ecommerce = Ecommerce::where('id', $result->id)->with('ecommerceSetting')->first();

        return $this->responseHelper->respond(['ecommerce' => $ecommerce], 'Settings upated successfully', 200);
    }

    public function setEcommerceColors(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'section' => ['required', 'string'],
            'color'   => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()], 400);
        }

        $color   = $request->color;
        $section = $request->section;

        $user              = $this->user();
        $ecommerceSettings = $user->ecommerceSettings;
        $colors            = $ecommerceSettings->ecommerce_colors;

        $colors = $colors[$section] = $color;
        $result = $user->ecommerceSettings()->update([
            'ecommerce_colors' => $colors,
        ]);

        if (! $result) {
            return $this->responseHelper->error('Request failed', ["Could not update the colors"], 500);
        }
        return $this->responseHelper->respond(['result' => $result, 'message' => " Colors have been updated successfully"], 'Request successful', 200);
    }

    public function setEcommerceSections(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'section' => ['required', 'string'],
            'color'   => ['required', 'string'],
            'name'    => ['required', 'string'],
            'content' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()], 400);
        }
        $file_url = null;
        if ($request->file) {

            $folder_name = 'ecommerce_sections/' . $request->section . "/" . $request->name;
            $file_url    = $this->saveFile($folder_name, $request->file);
        }

        $color   = $request->color;
        $section = $request->section;

        $user               = $this->user();
        $ecommerceSettings  = $user->ecommerceSettings;
        $ecommerce_sections = $ecommerceSettings->ecommerce_sections;

        $ecommerce_sections[count($ecommerce_sections)] = [
            'section' => $request->section,
            'color'   => $request->color,
            'name'    => $request->name,
            'content' => $request->content,
        ];

        $colors = $colors[$section] = $color;
        $result = $user->ecommerceSettings()->update([
            'ecommerce_sections' => $ecommerce_sections,
        ]);

        if (! $result) {
            return $this->responseHelper->error('Request failed', ["Could not update the colors"], 500);
        }
        return $this->responseHelper->respond(['result' => $result, 'message' => "Colors have been updated successfully"], 'Request successful', 200);
    }

    public function moveEcommerceSectionUp(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'section_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()], 400);
        }

        $user               = $this->user();
        $ecommerceSettings  = $user->ecommerceSettings;
        $ecommerce_sections = $ecommerceSettings->ecommerce_sections;

        $sectiont_to_move_up   = $ecommerce_sections[$request->section_id];
        $sectiont_to_move_down = $ecommerce_sections[$request->section_id - 1];

        $ecommerce_sections[$request->section_id - 1] = $sectiont_to_move_up;
        $ecommerce_sections[$request->section_id]     = $sectiont_to_move_down;

        $result = $user->ecommerceSettings()->update([
            'ecommerce_sections' => $ecommerce_sections,
        ]);

        if (! $result) {
            return $this->responseHelper->error('Request failed', ["Could not update the colors"], 500);
        }
        return $this->responseHelper->respond(['result' => $result, 'message' => "Colors have been updated successfully"], 'Request successful', 200);
    }

    public function moveEcommerceSectionDown(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'section_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()], 400);
        }

        $user               = $this->user();
        $ecommerceSettings  = $user->ecommerceSettings;
        $ecommerce_sections = $ecommerceSettings->ecommerce_sections;

        $sectiont_to_move_up   = $ecommerce_sections[$request->section_id];
        $sectiont_to_move_down = $ecommerce_sections[$request->section_id - 1];

        $ecommerce_sections[$request->section_id - 1] = $sectiont_to_move_up;
        $ecommerce_sections[$request->section_id]     = $sectiont_to_move_down;

        $result = $user->ecommerceSettings()->update([
            'ecommerce_sections' => $ecommerce_sections,
        ]);

        if (! $result) {
            return $this->responseHelper->error('Request failed', ["Could not update the colors"], 500);
        }
        return $this->responseHelper->respond(['result' => $result, 'message' => "Colors have been updated successfully"], 'Request successful', 200);
    }

    public function deleteEcommerceSection(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'section_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()], 400);
        }

        $user               = $this->user();
        $ecommerceSettings  = $user->ecommerceSettings;
        $ecommerce_sections = $ecommerceSettings->ecommerce_sections;

        // $sectiont_to_move_up = $ecommerce_sections[$request->section_id];

        for ($i = $request->section_id; $i < count($ecommerce_sections); $i++) {
            $ecommerce_sections[$i] = $ecommerce_sections[$i + 1];
        }

        $result = $user->ecommerceSettings()->update([
            'ecommerce_sections' => $ecommerce_sections,
        ]);

        if (! $result) {
            return $this->responseHelper->error('Request failed', ["Could not update the colors"], 500);
        }
        return $this->responseHelper->respond(['result' => $result, 'message' => "Colors have been updated successfully"], 'Request successful', 200);
    }

    public function notificationChannels()
    {

    }

    public function getNotifiedWhen()
    {

    }

    public function allowEcommerceDiscounts(Request $request)
    {
        $user = $this->user();

        $validator = Validator::make(request()->all(), [
            'allow_discounts' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()], 400);
        }

        $result = $user->ecommerceSettings()->update([
            'allow_discounts' => $request->allow_discounts,
        ]);

        if (! $result) {
            return $this->responseHelper->error('Request failed', ["Could not update the discount settings"], 500);
        }
        return $this->responseHelper->respond(['result' => $result, 'message' => "Discount settings have been updated"], 'Request successful', 200);

    }

    public function showSupportContactSettings(Request $request)
    {
        $user = $this->user();

        $validator = Validator::make(request()->all(), [
            'show_support_contact' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()], 400);
        }

        $result = $user->ecommerceSettings()->update([
            'show_support_contact' => $request->show_support_contact,
        ]);

        if (! $result) {
            return $this->responseHelper->error('Request failed', ["Could not update the support contact settings"], 500);
        }
        return $this->responseHelper->respond(['result' => $result, 'message' => "Support contact settings have been updated"], 'Request successful', 200);
    }

    public function allowEcommerceSitePayments(Request $request)
    {

        $user = $this->user();

        $validator = Validator::make(request()->all(), [
            'allow_payments' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()], 400);
        }

        $result = $user->ecommerceSettings()->update([
            'allow_payments' => $request->allow_payments,
        ]);

        if (! $result) {
            return $this->responseHelper->error('Request failed', ["Could not update the ecommerce payment settings"], 500);
        }
        return $this->responseHelper->respond(['result' => $result, 'message' => "Payments have been allowed"], 'Request successful', 200);

    }

    public function setAgeRestrictions(Request $request)
    {

        $user = $this->user();

        $validator = Validator::make(request()->all(), [
            'is_age_restricted' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()], 400);
        }

        $result = $user->ecommerceSettings()->update([
            'is_age_restricted' => $request->is_age_restricted,
        ]);

        if (! $result) {
            return $this->responseHelper->error('Request failed', ["Could not update the age restriction settings"], 500);
        }
        return $this->responseHelper->respond(['result' => $result, 'message' => "Age restrictions have been set"], 'Request successful', 200);

    }

    public function collectCustomerInformation()
    {

    }

}

<?php

namespace App\Http\Controllers;

use App\Models\SaleSetting;
use App\Http\Requests\StoreSaleSettingRequest;
use App\Http\Requests\UpdateSaleSettingRequest;

class SaleSettingController extends Basecontroller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $saleSetting = $this->account()->saleSettings;

        if (! $saleSetting) {
            return $this->sendError("Bad request", ["error" => "The sale settings could not be fetched.", "result" => $saleSetting]);
        }

        return $this->sendResponse(["sale_settings" => $saleSetting], "success, The sale settings has been fetched successfully.");

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

        $settings = [
            "currency"                  => SaleSetting::CURRENCY,
            'stock_required_when_below' => SaleSetting::REQUIRED_WHEN_BELOW,
            'VAT_percentage'            => SaleSetting::VAT_PERCENTAGE,
        ];

        if (! $settings) {
            return $this->sendError("Bad request", ["error" => "The sale settings could not be fetched.", "result" => $settings]);
        }

        return $this->sendResponse(["settings" => $settings], "success, The sale settings has been fetched successfully.");

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $validator = Validator::make($request->all(),
            [
                "allow_discounts"           => ["required"],
                "allow_online_payments"     => ["required"],
                "show_all_products"         => ["required"],
                "adjust_for_VAT"            => ["required"],
                "VAT_Percentage"            => ["required"],
                "currency"                  => ["required"],
                "stock_required_when_below" => ["required"],
            ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The settings could not be saved .", "result" => $validator->errors()]);
        }

        $validated = $validator->validated();

        $saleSetting = $this->account()->saleSettings()->create(
            [
                "user_id"                   => $this->user()->id,
                "allow_discounts"           => $validated["allow_discounts"],
                "allow_online_payments"     => $validated["allow_online_payments"],
                "show_all_products"         => $validated["show_all_products"],
                "adjust_for_VAT"            => $validated["adjust_for_VAT"],
                "VAT_Percentage"            => $validated["VAT_Percentage"],
                "currency"                  => $validated["currency"],
                "stock_required_when_below" => $validated["stock_required_when_below"],

            ]
        );

        if (! $saleSetting) {
            return $this->sendError("Bad request", ["error" => "The sale settings could not be saved.", "result" => $saleSetting]);
        }

        return $this->sendResponse(["saleSettings" => $saleSetting], "success, The sale settings has been saved successfully.");

    }

    /**
     * Display the specified resource.
     */
    public function show($saleSetting)
    {
        //

        $saleSetting = $this->account()->saleSettings()->where('id', $saleSetting)->first();

        if (! $saleSetting) {
            return $this->sendError("Bad request", ["error" => "The sale settings could not be found.", "result" => $saleSetting]);
        }

        return $this->sendResponse(["saleSettings" => $saleSetting], "success, The sale settings has been fetched successfully.");

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($saleSetting)
    {
        //

        $saleSetting = $this->account()->saleSettings()->where('id', $saleSetting)->first();

        $settings = [
            "currency"                  => SaleSetting::CURRENCY,
            'stock_required_when_below' => SaleSetting::REQUIRED_WHEN_BELOW,
            'VAT_percentage'            => SaleSetting::VAT_PERCENTAGE,
        ];

        if (! $saleSetting) {
            return $this->sendError("Bad request", ["error" => "The sale settings could not be found.", "result" => $saleSetting]);
        }

        return $this->sendResponse(["saleSettings" => $saleSetting, 'settings' => $settings], "success, The sale settings has been fetched successfully.");

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $saleSetting)
    {
        //

        $saleSetting = $this->account()->saleSettings()->where('id', $saleSetting)->first();

        if (! $saleSetting) {
            return $this->sendError("Bad request", ["error" => "The sale settings could not be found.", "result" => $saleSetting]);
        }

        $validator = Validator::make($request->all(),
            [
                "allow_discounts"           => ["required"],
                "allow_online_payments"     => ["required"],
                "show_all_products"         => ["required"],
                "adjust_for_VAT"            => ["required"],
                "VAT_Percentage"            => ["required"],
                "currency"                  => ["required"],
                "stock_required_when_below" => ["required"],
            ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The settings could not be saved .", "result" => $validator->errors()]);
        }

        $validated = $validator->validated();

        $saleSetting = $saleSetting->update(
            [
                "user_id"                   => $this->user()->id,
                "allow_discounts"           => $validated["allow_discounts"],
                "allow_online_payments"     => $validated["allow_online_payments"],
                "show_all_products"         => $validated["show_all_products"],
                "adjust_for_VAT"            => $validated["adjust_for_VAT"],
                "VAT_Percentage"            => $validated["VAT_Percentage"],
                "currency"                  => $validated["currency"],
                "stock_required_when_below" => $validated["stock_required_when_below"],

            ]
        );

        if (! $saleSetting) {
            return $this->sendError("Bad request", ["error" => "The sale settings could not be updated.", "result" => $saleSetting]);
        }

        return $this->sendResponse(["saleSettings" => $saleSetting], "success, The sale settings has been updated successfully.");

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($saleSetting)
    {
        //

        $saleSetting = SaleSetting::destroy($saleSetting);

        if (! $saleSetting) {
            return $this->sendError("Bad request", ["error" => "The sale settings could not be deleted.", "result" => $saleSetting]);
        }

        return $this->sendResponse(["saleSettings" => $saleSetting], "success, The sale settings has been deleted successfully.");

    }
}

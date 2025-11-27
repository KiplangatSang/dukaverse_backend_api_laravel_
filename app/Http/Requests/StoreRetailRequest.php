<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            "retail_name"  => "required|string|min:3",
            "retail_goods" => "required|array",
            "retail_town"=>"required|string|min:3",
            "retail_constituency"=>"required|string|min:3",
            "retail_county"=>"required|string|min:3",
        ];
    }
}

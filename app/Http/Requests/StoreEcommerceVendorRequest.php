<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEcommerceVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization logic will be handled in Controller.
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id', // Owner user
            // Add more fields and validation rules as needed.
        ];
    }
}

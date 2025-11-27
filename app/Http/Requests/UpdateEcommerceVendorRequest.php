<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEcommerceVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization logic handled in Controller
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'sometimes|required|exists:users,id',
            // Additional fields and rules as needed
        ];
    }
}

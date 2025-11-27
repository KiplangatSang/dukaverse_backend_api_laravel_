<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ownerable_type' => 'required|string',
            'ownerable_id' => 'required|integer',
            'accountable_type' => 'nullable|string',
            'accountable_id' => 'nullable|integer',
            'account' => 'required|string',
            'account_ref' => 'required|string',
            'balance' => 'numeric',
            'last_balance' => 'nullable|string',
            'on_hold' => 'nullable|string',
            'initial_deposit' => 'nullable|string',
            'max_amount' => 'nullable|numeric',
            'min_amount' => 'nullable|numeric',
            'account_status' => 'boolean',
            'is_active' => 'boolean',
            'account_type' => 'string',
        ];
    }
}

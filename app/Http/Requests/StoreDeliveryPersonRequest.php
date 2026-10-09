<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryPersonRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:delivery_people,email',
            'phone' => 'required|string|unique:delivery_people,phone',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|string|in:male,female,other',
            'bio' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'id_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'license_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'nullable|string|in:pending,active,suspended,banned,inactive',
            'delivery_preferences' => 'nullable|json',
            'working_hours' => 'nullable|json',
            'max_delivery_distance' => 'nullable|numeric|min:1|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already registered',
            'phone.required' => 'Phone number is required',
            'phone.unique' => 'This phone number is already registered',
            'profile_photo.image' => 'Profile photo must be an image',
            'profile_photo.mimes' => 'Profile photo must be a JPEG, PNG, or JPG file',
            'profile_photo.max' => 'Profile photo must not exceed 2MB',
            'id_photo.image' => 'ID photo must be an image',
            'id_photo.mimes' => 'ID photo must be a JPEG, PNG, or JPG file',
            'id_photo.max' => 'ID photo must not exceed 2MB',
            'license_photo.image' => 'License photo must be an image',
            'license_photo.mimes' => 'License photo must be a JPEG, PNG, or JPG file',
            'license_photo.max' => 'License photo must not exceed 2MB',
        ];
    }
}

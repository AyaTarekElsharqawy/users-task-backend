<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    // Allow all users to make this request (can be modified if needed)
    public function authorize(): bool
    {
        return true;
    }

    // Define validation rules for updating a user
    public function rules(): array
    {
        return [
            // Name is optional, but if present must be a string with max length 255
            'name' => 'sometimes|string|max:255',

            // Email is optional, must be valid format and unique except for the current user
            'email' => 'sometimes|email|unique:users,email,'.$this->user->id,

            // Password is optional, must be at least 6 characters and confirmed
            'password' => 'sometimes|string|min:6|confirmed',

            // Role is optional, but if present must be either 'admin' or 'user'
            'role' => 'sometimes|in:admin,user',
        ];
    }
}

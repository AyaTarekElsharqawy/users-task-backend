<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    // Allow all users to make this request (can be customized)
    public function authorize(): bool
    {
        return true;
    }

    // Define validation rules for storing a new user
    public function rules(): array
    {
        return [
            // Name is required, must be a string, and max 255 characters
            'name' => 'required|string|max:255',

            // Email is required, must be valid, and unique in users table
            'email' => 'required|email|unique:users',

            // Password is required, at least 6 characters, must be confirmed, and alphanumeric only
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                'regex:/^[A-Za-z0-9]+$/'
            ],

            // Role must be either 'admin' or 'user'
            'role' => 'required|in:admin,user',
        ];
    }
}

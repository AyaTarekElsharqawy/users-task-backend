<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => [
    'required',
    'string',
    'min:6',
    'confirmed',
    'regex:/^[A-Za-z0-9]+$/'
],

            'role' => 'required|in:admin,user',
        ];
    }
}
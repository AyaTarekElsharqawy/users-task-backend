<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        // Validate incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->whereNull('deleted_at'),
            ],
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,user',
        ]);

        // Hash the password
        $validated['password'] = Hash::make($validated['password']);

        // Check if the user exists and is soft-deleted
        $user = User::withTrashed()
            ->where('email', $validated['email'])
            ->first();

        // If user is soft-deleted, restore and update it
        if ($user && $user->trashed()) {
            $user->restore();
            $user->update($validated);
        } else {
            // Otherwise, create a new user
            $user = User::create($validated);
        }

        // Create authentication token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return response with token and user info
        return response()->json([
            'success' => true,
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    // Login user and issue token
    public function login(Request $request)
    {
        // Validate login credentials
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and password matches
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Delete old tokens (optional but improves security)
        $user->tokens()->delete();

        // Generate new token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return response with token and user info
        return response()->json([
            'success' => true,
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    // Logout the current user by deleting the current token
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        // Return logout confirmation
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    // Return the authenticated user's information
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    }
}

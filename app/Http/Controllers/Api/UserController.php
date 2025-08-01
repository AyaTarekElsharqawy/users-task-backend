<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    
    public function index(): JsonResponse
    {
        $users = User::withTrashed()->paginate(10);
        
       
        $formattedUsers = $users->getCollection()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at?->format('Y-m-d H:i:s'),
                'deleted_at' => $user->deleted_at?->format('Y-m-d H:i:s'),
                'is_admin' => $user->isAdmin(),
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $formattedUsers,
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem()
            ]
        ]);
    }

    
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->whereNull('deleted_at'),
            ],
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,user',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        
        $user = User::create($validated);
        
        $formattedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at?->format('Y-m-d H:i:s'),
            'is_admin' => $user->isAdmin(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $formattedUser,
            'message' => 'User created successfully'
        ], 201);
    }

    
    public function show(User $user): JsonResponse
    {
        $formattedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $user->deleted_at?->format('Y-m-d H:i:s'),
            'is_admin' => $user->isAdmin(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $formattedUser
        ]);
    }

   
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)->whereNull('deleted_at'),
            ],
            'password' => 'sometimes|string|min:6|confirmed',
            'role' => 'sometimes|required|in:admin,user',
        ]);
        
        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }
        
        $user->update($validated);
        
        $formattedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at?->format('Y-m-d H:i:s'),
            'is_admin' => $user->isAdmin(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $formattedUser,
            'message' => 'User updated successfully'
        ]);
    }

    
    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }


    public function restore($id): JsonResponse
    {
        $user = User::withTrashed()->find($id);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        if (!$user->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'User is not deleted'
            ], 400);
        }
        
        $user->restore();
        
        return response()->json([
            'success' => true,
            'message' => 'User restored successfully'
        ]);
    }
}
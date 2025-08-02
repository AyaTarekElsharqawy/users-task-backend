<?php   

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller 
{
    // Return a paginated list of all users including soft-deleted ones
    public function index(): JsonResponse
    {
        $users = User::withTrashed()->paginate(10);

        // Format each user in the collection
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

        // Return formatted data with pagination info
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

    // Store a newly created user
    public function store(Request $request): JsonResponse
    {
        // Validate incoming request data
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

        // Encrypt password
        $validated['password'] = bcrypt($validated['password']);

        // Create user
        $user = User::create($validated);

        // Format created user
        $formattedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at?->format('Y-m-d H:i:s'),
            'is_admin' => $user->isAdmin(),
        ];

        // Return response
        return response()->json([
            'success' => true,
            'data' => $formattedUser,
            'message' => 'User created successfully'
        ], 201);
    }

    // Show a specific user
    public function show(User $user): JsonResponse
    {
        // Format user
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

        // Return user data
        return response()->json([
            'success' => true,
            'data' => $formattedUser
        ]);
    }

    // Update an existing user
    public function update(Request $request, User $user): JsonResponse
    {
        // Validate request data
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

        // Encrypt password if provided
        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        // Update user data
        $user->update($validated);

        // Format updated user
        $formattedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at?->format('Y-m-d H:i:s'),
            'is_admin' => $user->isAdmin(),
        ];

        // Return response
        return response()->json([
            'success' => true,
            'data' => $formattedUser,
            'message' => 'User updated successfully'
        ]);
    }

    // Soft delete a user
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    // Restore a soft-deleted user
    public function restore($id): JsonResponse
    {
        $user = User::withTrashed()->find($id);

        // Check if user exists
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Check if user is actually deleted
        if (!$user->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'User is not deleted'
            ], 400);
        }

        // Restore user
        $user->restore();

        // Return success message
        return response()->json([
            'success' => true,
            'message' => 'User restored successfully'
        ]);
    }
}

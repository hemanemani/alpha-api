<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/user",
 *     summary="Get authenticated user details",
 *     description="Retrieve the information of the logged-in user.",
 *     operationId="user",
 *     tags={"User"},
 *     security={{ "bearerAuth": {} }},
 *     @OA\Response(
 *         response=200,
 *         description="User details retrieved successfully.",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="John"),
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated.")
 * )
 */

    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    /**
     * Store a newly created user in storage.
     */

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create a new user",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */

     public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->status = $request->status;
        $user->save();

        return response()->json(["message" => "User status updated successfully"]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'user_name' => 'required|string|max:255',
            'mobile_number' => 'nullable|string|max:255',
            'access_level' => 'required|in:full,view,limited',
            'allowed_pages' => 'nullable|array',
            'is_admin'      => 'integer'
        ]);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'user_name' => $validated['user_name'],
            'mobile_number' => $validated['mobile_number'],
            'access_level' => $validated['access_level'],
            'allowed_pages' => $validated['access_level'] === 'limited' ? $validated['allowed_pages'] : [],
            'is_admin'  => $request->is_admin,

        ]);
             // Log the user created
        return response()->json([
            'success' => true,
            'message' => 'User Created Successfully',
            'user' => $user
        ], 201);
        

    }
    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get details of a specific user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to retrieve",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User retrieved successfully."),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="created_at", type="string", example="2025-01-22T08:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-01-22T08:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     )
     * )
     */

    public function show(User $user)
    {
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'user' => $user,
        ], 200);
    }
    
   
    /**
     * Update the specified user in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update an existing user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'password' => 'nullable|confirmed|min:8',
            'user_name' => 'required|string|max:255',
            'mobile_number' => 'nullable|string|max:255',
            'access_level' => 'required|in:full,view,limited',
            'allowed_pages' => 'nullable|array',
            'is_admin'      => 'integer'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'user_name' => $request->user_name,
            'access_level' => $request->access_level,
            'allowed_pages' => $request->access_level === 'limited' ? $request->allowed_pages : [], // Check the access_level of the user being updated
            'is_admin'  => $request->is_admin,
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => bcrypt($request->password),
            ]);
            $user->tokens()->delete();

        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'user' =>$user
        ], 200);

    }


    /**
     * Remove the specified user from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ], 200);
        }
}

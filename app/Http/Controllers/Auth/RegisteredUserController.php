<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class RegisteredUserController extends Controller
{
    /**
 * @OA\Post(
 *     path="/api/register",
 *     summary="Register a new user",
 *     description="Create a new user account.",
 *     operationId="register",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email", "password", "password_confirmation"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User created successfully.",
 *         @OA\JsonContent(
 *             @OA\Property(property="access_token", type="string", example="1|abc123"),
 *             @OA\Property(property="token_type", type="string", example="Bearer"),
 *             @OA\Property(property="user", type="object")
 *         )
 *     ),
 *     @OA\Response(response=422, description="Validation error.")
 * )
 */

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users'],
            'password' => 'required|string|confirmed|min:8',
            'user_name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:255',
            'access_level' => 'required|in:full,view,limited'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile_number' => $request->mobile_number,
            'user_name' => $request->user_name,
            'access_level' => $request->access_level
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }
}

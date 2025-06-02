<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Services\ActivityLoggerService;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', Password::min(8)],
            'full_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
        ]);

        $userResponse = $user->only(['id', 'username', 'email', 'full_name', 'secure_url_profile', 'public_url_profile', 'role', 'status', 'created_at', 'updated_at']);

        ActivityLoggerService::log('register', 'User ' . $user->email . ' (ID: ' . $user->id . ') registered.', $user->id, $request);

        return response()->json(
            [
                'message' => 'User registered successfully',
                'user' => $userResponse,
            ],
            201,
        );
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $accessToken = $user->createToken('auth_token')->plainTextToken;

        $userResponse = $user->only(['id', 'username', 'email', 'phone', 'secure_url_profile', 'public_url_profile', 'role', 'status', 'otp', 'created_at', 'updated_at']);

        return response()->json([
            'message' => 'Login successful',
            'accessToken' => $accessToken,
            'user' => $userResponse,
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user()->makeHidden('password');
        return response()->json([
            'statusCode' => 200,
            'message' => 'User retrieved successfully',
            'data' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}

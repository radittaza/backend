<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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
            return response()->json(['success' => false, 'message' => 'Validation errors', 'errors' => $validator->errors()], 422);
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
                'statusCode' => 201,
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $userResponse,
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
            return response()->json(['success' => false, 'message' => 'Validation errors', 'errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['success' => false, 'message' => 'Invalid login details'], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $accessToken = $user->createToken('auth_token')->plainTextToken;
        $userResponse = $user->only(['id', 'username', 'email', 'full_name', 'phone', 'secure_url_profile', 'public_url_profile', 'role', 'status', 'otp', 'created_at', 'updated_at']);
        ActivityLoggerService::log('login', 'User ' . $user->email . ' (ID: ' . $user->id . ') logged in.', $user->id, $request);

        return response()->json([
            'statusCode' => 200,
            'success' => true,
            'message' => 'Login successful',
            'accessToken' => $accessToken,
            'data' => $userResponse,
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        $userResponse = $user->only(['id', 'username', 'email', 'full_name', 'phone', 'secure_url_profile', 'public_url_profile', 'role', 'status', 'otp', 'created_at', 'updated_at']);
        return response()->json([
            'statusCode' => 200,
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => $userResponse,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        ActivityLoggerService::log('logout', 'User ' . $user->email . ' (ID: ' . $user->id . ') logged out.', $user->id, $request);
        $user->currentAccessToken()->delete();
        return response()->json(['statusCode' => 200, 'success' => true, 'message' => 'Logged out successfully']);
    }

    public function updateUser(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|required|string|max:255',
            'username' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation errors', 'errors' => $validator->errors()], 422);
        }

        $updateData = $request->only(['full_name', 'username', 'phone']);
        if (empty($updateData)) {
            return response()->json(['success' => false, 'message' => 'No data provided for update.'], 400);
        }

        $user->update($updateData);
        $userResponse = $user->only(['id', 'username', 'email', 'full_name', 'phone', 'secure_url_profile', 'public_url_profile', 'role', 'status', 'otp', 'created_at', 'updated_at']);
        ActivityLoggerService::log('update_profile', 'User ' . $user->email . ' (ID: ' . $user->id . ') updated their profile.', $user->id, $request);

        return response()->json([
            'statusCode' => 200,
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $userResponse
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation errors', 'errors' => $validator->errors()], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password does not match.'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();
        ActivityLoggerService::log('update_password', 'User ' . $user->email . ' (ID: ' . $user->id . ') updated their password.', $user->id, $request);

        return response()->json(['statusCode' => 200, 'success' => true, 'message' => 'Password updated successfully']);
    }

    public function updateProfilePicture(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // Maks 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation errors', 'errors' => $validator->errors()], 422);
        }

        if ($user->secure_url_profile) {
             $oldPath = str_replace(Storage::url(''), '', $user->secure_url_profile);
             if (str_starts_with($oldPath, 'profile_pictures/')) { // Hanya hapus jika di folder yang benar
                Storage::disk('public')->delete($oldPath);
             }
        }

        $path = $request->file('profile_image')->store('profile_pictures', 'public');
        $user->secure_url_profile = Storage::url($path);
        $user->public_url_profile = Storage::url($path);
        $user->save();

        $userResponse = $user->only(['id', 'username', 'email', 'full_name', 'phone', 'secure_url_profile', 'public_url_profile', 'role', 'status', 'otp', 'created_at', 'updated_at']);
        ActivityLoggerService::log('update_profile_picture', 'User ' . $user->email . ' (ID: ' . $user->id . ') updated their profile picture.', $user->id, $request);

        return response()->json([
            'statusCode' => 200,
            'success' => true,
            'message' => 'Profile picture updated successfully',
            'data' => $userResponse
        ]);
    }
}

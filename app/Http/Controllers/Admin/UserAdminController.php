<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;


class UserAdminController extends Controller
{
    public function index(Request $request)
    {

        $search = $request->query('search');
        $users = User::when($search, function ($query, $search) {
            return $query
                ->where('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('full_name', 'like', "%{$search}%");
        })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.users.index', compact('users', 'search'));
    }

    public function create()
    {
        $roles = ['user', 'admin'];
        $statuses = ['active', 'pending', 'suspend'];
        return view('admin.users.create_edit', compact('roles', 'statuses'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'full_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::min(8)], // Menggunakan Rules\Password
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,admin',
            'status' => 'required|in:active,pending,suspend',
        ]);

        $userData = $validatedData;
        $userData['password'] = Hash::make($request->password);
        User::create($userData);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }


    public function edit(User $user)
    {
        $roles = ['user', 'admin'];
        $statuses = ['active', 'pending', 'suspend'];
        return view('admin.users.create_edit', compact('user', 'roles', 'statuses'));
    }

    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'full_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::min(8)], /
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,admin',
            'status' => 'required|in:active,pending,suspend',
        ]);

        $userData = $validatedData;

        if (!empty($request->password)) {
            $userData['password'] = Hash::make($request->password);
        } else {
            unset($userData['password']);
        }

        $user->update($userData);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }


    public function destroy(User $user)
    {
        if (Auth::id() == $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account.');
        }

        $userName = $user->username;
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "User '{$userName}' deleted successfully.");
    }
}

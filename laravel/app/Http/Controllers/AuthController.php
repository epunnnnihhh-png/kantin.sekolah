<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class AuthController extends Controller
{
    public function loginApi(Request $request)
    {
        \App\Services\CanteenManager::initDatabase();

        $loginInput = trim($request->input('login') ?: $request->input('email') ?: $request->input('username') ?: '');
        $password = $request->input('password');

        if (empty($loginInput) || empty($password)) {
            return response()->json(['success' => false, 'message' => 'Harap masukkan username/email dan password.'], 400);
        }

        // Auto-seed/verify staff accounts if a staff/admin login is attempted
        if (str_starts_with(strtolower($loginInput), 'kantin') || str_starts_with(strtolower($loginInput), 'admin')) {
            \App\Services\CanteenManager::seedStaffAccounts();
        }

        $user = User::where(function($query) use ($loginInput) {
            $query->where('email', strtolower($loginInput))
                  ->orWhere('username', strtolower($loginInput))
                  ->orWhere('username', $loginInput)
                  ->orWhere('name', $loginInput);
        })->first();

        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user, true);
            session([
                'is_logged_in' => true, 
                'user_id' => $user->id,
                'user_role' => $user->role, 
                'canteen_id' => $user->canteen_id,
                'user_name' => $user->name,
                'username' => $user->username ?: $user->name
            ]);

            $canteenInfo = $user->canteen_id ? \App\Services\CanteenManager::getCanteenInfo($user->canteen_id) : null;

            return response()->json([
                'success' => true, 
                'role' => $user->role,
                'name' => $user->name,
                'username' => $user->username ?: $user->name,
                'canteen_id' => $user->canteen_id,
                'canteen_name' => $canteenInfo ? $canteenInfo['name'] : ($user->role === 'admin' ? 'Semua Kantin' : null),
                'class_name' => $user->class_name ?? 'XII RPL 1',
                'balance' => $user->balance ?? 50000,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username ?: $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'canteen_id' => $user->canteen_id,
                    'class_name' => $user->class_name ?? 'XII RPL 1',
                    'balance' => $user->balance ?? 50000
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Email/Username atau password salah.'], 401);
    }

    public function registerApi(Request $request)
    {
        $name = trim($request->input('name') ?: '');
        $username = trim($request->input('username') ?: '');
        $email = strtolower(trim($request->input('email') ?: ''));
        $password = $request->input('password');
        $className = $request->input('class_name', 'XII RPL 1');

        if (empty($name) || empty($password)) {
            return response()->json(['success' => false, 'message' => 'Nama lengkap dan password wajib diisi.'], 400);
        }

        if (empty($username)) {
            $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', $name)[0])) ?: 'siswa' . rand(100, 999);
        }

        if (User::where('username', $username)->exists()) {
            $username = $username . rand(10, 99);
        }

        if (empty($email)) {
            $email = $username . '@kantin.com';
        }

        if (User::where('email', $email)->exists()) {
            return response()->json(['success' => false, 'message' => 'Email sudah terdaftar. Silakan login atau gunakan email lain.'], 400);
        }

        $user = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'student',
            'class_name' => $className,
            'balance' => 50000,
        ]);

        Auth::login($user, true);
        session([
            'is_logged_in' => true,
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_name' => $user->name,
            'username' => $user->username
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Pendaftaran akun berhasil! Selamat datang, ' . $user->name,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'class_name' => $user->class_name,
                'balance' => $user->balance
            ]
        ]);
    }

    public function logoutApi(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['success' => true]);
    }
}

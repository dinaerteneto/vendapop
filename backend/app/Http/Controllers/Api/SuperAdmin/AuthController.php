<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !$user->is_super_admin) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (!$user->is_super_admin) {
                Auth::logout();
                return response()->json(['message' => 'Credenciais inválidas'], 401);
            }

            $user->update(['last_login_at' => now()]);

            $token = $user->createToken('admin_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_super_admin' => true,
                ],
            ]);
        }

        return response()->json(['message' => 'Credenciais inválidas'], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}

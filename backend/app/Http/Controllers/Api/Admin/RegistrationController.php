<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_slug' => 'required|string|max:255|unique:tenants,slug',
            'whatsapp_number' => 'required|string',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create Tenant
        $tenant = Tenant::create([
            'name' => $validated['store_name'],
            'slug' => Str::slug($validated['store_slug']),
            'whatsapp_number' => $validated['whatsapp_number'],
            'primary_color' => '#7c3aed', // Default purple
            'secondary_color' => '#f3e8ff',
        ]);

        // Create Admin User
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin',
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_owner' => true,
        ]);

        $token = $user->createToken('admin_token')->plainTextToken;

        return response()->json([
            'message' => 'Store created successfully',
            'token' => $token,
            'user' => $user,
            'tenant' => $tenant,
        ], 201);
    }
}


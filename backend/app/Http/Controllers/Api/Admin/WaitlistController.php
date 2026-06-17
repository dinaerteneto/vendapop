<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WaitlistConfirmationMail;
use App\Models\WaitlistEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class WaitlistController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $exists = WaitlistEntry::where('email', $validated['email'])->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Você já está na lista de espera! Entraremos em contato em breve.',
            ]);
        }

        WaitlistEntry::create(['email' => $validated['email']]);

        Mail::to($validated['email'])->send(new WaitlistConfirmationMail($validated['email']));

        return response()->json([
            'message' => 'Email cadastrado com sucesso! Entraremos em contato.',
        ], 201);
    }
}

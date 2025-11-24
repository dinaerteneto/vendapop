<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushSubscriptionController extends Controller
{
    /**
     * Register a push subscription for the authenticated user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|url',
            'keys' => 'required|array',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $user = $request->user();

        try {
            // Check if subscription already exists
            $existing = PushSubscription::where('user_id', $user->id)
                ->where('endpoint', $validated['endpoint'])
                ->first();

            if ($existing) {
                // Update existing subscription
                $existing->update([
                    'public_key' => $validated['keys']['p256dh'],
                    'auth_token' => $validated['keys']['auth'],
                ]);

                return response()->json([
                    'message' => 'Subscription atualizada com sucesso',
                    'subscription' => $existing,
                ], 200);
            }

            // Create new subscription
            $subscription = PushSubscription::create([
                'user_id' => $user->id,
                'endpoint' => $validated['endpoint'],
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
            ]);

            return response()->json([
                'message' => 'Subscription registrada com sucesso',
                'subscription' => $subscription,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erro ao registrar push subscription: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao registrar subscription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a push subscription
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $subscription = PushSubscription::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Subscription não encontrada',
            ], 404);
        }

        $subscription->delete();

        return response()->json([
            'message' => 'Subscription removida com sucesso',
        ], 200);
    }
}


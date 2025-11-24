<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerPushSubscription;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerPushSubscriptionController extends Controller
{
    /**
     * Register a push subscription for a customer order
     */
    public function store(Request $request, $storeSlug, $orderUuid)
    {
        $validated = $request->validate([
            'endpoint' => 'required|url',
            'keys' => 'required|array',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        // Verify order exists and belongs to tenant
        $order = Order::where('uuid', $orderUuid)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Verify order belongs to tenant with this slug
        $tenant = $order->tenant;
        if ($tenant->slug !== $storeSlug) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        try {
            // Check if subscription already exists
            $existing = CustomerPushSubscription::where('order_uuid', $orderUuid)
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
            $subscription = CustomerPushSubscription::create([
                'order_uuid' => $orderUuid,
                'endpoint' => $validated['endpoint'],
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
            ]);

            return response()->json([
                'message' => 'Subscription registrada com sucesso',
                'subscription' => $subscription,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erro ao registrar push subscription do cliente: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao registrar subscription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a push subscription
     */
    public function destroy(Request $request, $storeSlug, $orderUuid, $id)
    {
        $subscription = CustomerPushSubscription::where('id', $id)
            ->where('order_uuid', $orderUuid)
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

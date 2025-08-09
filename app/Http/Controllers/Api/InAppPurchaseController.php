<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessSubscription;
use App\Models\ClientSubscription;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InAppPurchaseController extends Controller
{
    // Get available subscription plans
    public function getPlans(Request $request)
    {
        $plans = BusinessSubscription::whereHas('subscriptionBuyerTypes', function($query) {
            $query->where('buyer_type_id', 1); // 1 for doctor
        })->get();

        return response()->json([
            'success' => true,
            'data' => $plans->map(function($plan) {
                return [
                    'id' => $plan->id,
                    'title' => $plan->name_en,
                    'description' => $plan->description_en,
                    'price' => $plan->cost,
                    'duration' => $plan->period,
                    'duration_type' => $plan->type, // 'month' or 'year'
                    'ios_plan_id' => $plan->ios_plan_id,
                    'android_plan_id' => $plan->android_plan_id,
                    'status' => $plan->status
                ];
            })
        ]);
    }

    // Verify and activate iOS purchase
    public function verifyIosPurchase(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:bussiness_subscriptions,id',
            'receipt_data' => 'required|string',
            'transaction_id' => 'required|string'
        ]);

        $client = $request->get('auth_user');
        $subscription = BusinessSubscription::find($request->subscription_id);

        // Verify receipt with Apple (simplified)
        $isValid = $this->verifyAppleReceipt($request->receipt_data);
        
        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid receipt'
            ], 400);
        }

        // Create subscription record
        $clientSubscription = ClientSubscription::create([
            'client_id' => $client->id,
            'bussiness_subscription_id' => $subscription->id,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => $subscription->type === 'year' ? now()->addYears($subscription->period) : now()->addMonths($subscription->period),
            'price' => $subscription->cost,
            'platform' => 'ios',
            'transaction_id' => $request->transaction_id,
            'receipt_data' => $request->receipt_data
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription activated successfully',
            'data' => [
                'subscription_id' => $clientSubscription->id,
                'expires_at' => $clientSubscription->end_date
            ]
        ]);
    }

    // Verify and activate Android purchase
    public function verifyAndroidPurchase(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:bussiness_subscriptions,id',
            'purchase_token' => 'required|string',
            'order_id' => 'required|string'
        ]);

        $client = $request->get('auth_user');
        $subscription = BusinessSubscription::find($request->subscription_id);

        // Verify purchase with Google Play (simplified)
        $isValid = $this->verifyGooglePlayPurchase($request->purchase_token);
        
        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid purchase token'
            ], 400);
        }

        // Create subscription record
        $clientSubscription = ClientSubscription::create([
            'client_id' => $client->id,
            'bussiness_subscription_id' => $subscription->id,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => $subscription->type === 'year' ? now()->addYears($subscription->period) : now()->addMonths($subscription->period),
            'price' => $subscription->cost,
            'platform' => 'android',
            'transaction_id' => $request->order_id,
            'receipt_data' => $request->purchase_token
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription activated successfully',
            'data' => [
                'subscription_id' => $clientSubscription->id,
                'expires_at' => $clientSubscription->end_date
            ]
        ]);
    }

    // Get user's active subscriptions
    public function getMySubscriptions(Request $request)
    {
        $client = $request->get('auth_user');
        
        $subscriptions = ClientSubscription::where('client_id', $client->id)
            ->with('subscription')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subscriptions->map(function($sub) {
                return [
                    'id' => $sub->id,
                    'plan_name' => $sub->subscription->name_en,
                    'status' => $sub->status,
                    'start_date' => $sub->start_date,
                    'end_date' => $sub->end_date,
                    'is_active' => $sub->isActive(),
                    'platform' => $sub->platform,
                    'price' => $sub->price
                ];
            })
        ]);
    }

    // Check subscription status
    public function checkSubscriptionStatus(Request $request)
    {
        $client = $request->get('auth_user');
        
        $activeSubscription = ClientSubscription::where('client_id', $client->id)
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->with('subscription')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'has_active_subscription' => !!$activeSubscription,
                'subscription' => $activeSubscription ? [
                    'plan_name' => $activeSubscription->subscription->name_en,
                    'expires_at' => $activeSubscription->end_date,
                    'days_remaining' => $activeSubscription->end_date->diffInDays(now())
                ] : null
            ]
        ]);
    }

    // Simplified Apple receipt verification (implement proper verification)
    private function verifyAppleReceipt($receiptData)
    {
        // TODO: Implement actual Apple receipt verification
        // This is a placeholder - you need to verify with Apple's servers
        return true; // For testing
    }

    // Simplified Google Play purchase verification (implement proper verification)
    private function verifyGooglePlayPurchase($purchaseToken)
    {
        // TODO: Implement actual Google Play purchase verification
        // This is a placeholder - you need to verify with Google Play API
        return true; // For testing
    }
}
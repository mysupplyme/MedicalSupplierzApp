<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionBuyerType;
use App\Models\Client;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function getSubscriptionPackages(Request $request)
    {
        $buyerType = $request->buyer_type ?: 'doctor';
        
        $subscriptions = BusinessSubscription::whereHas('subscriptionBuyerTypes', function($query) use ($buyerType) {
            $query->where('buyer_type', $buyerType);
        })->with('subscriptionBuyerTypes')->get();
        
        return response()->json([
            'success' => true,
            'data' => $subscriptions
        ]);
    }
    
    public function subscribe(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'subscription_id' => 'required|exists:bussiness_subscriptions,id'
        ]);
        
        $client = Client::find($request->client_id);
        $subscription = BusinessSubscription::find($request->subscription_id);
        
        // Update client with subscription info
        $client->update([
            'subscription_id' => $request->subscription_id,
            'subscription_start' => now(),
            'subscription_end' => now()->addDays($subscription->duration ?? 30)
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Successfully subscribed to ' . $subscription->title,
            'data' => $client
        ]);
    }
}
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

        // Verify transaction with Apple App Store Server API
        $isValid = $this->verifyAppleReceipt($request->transaction_id);
        
        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid receipt'
            ], 400);
        }

        // Create subscription record
        $clientSubscription = ClientSubscription::create([
            'client_id' => $client->id,
            'subscription_id' => $subscription->id,
            'status' => 'active',
            'start_at' => now()->toDateString(),
            'end_at' => $subscription->type === 'year' ? now()->addYears($subscription->period)->toDateString() : now()->addMonths($subscription->period)->toDateString(),
            'platform' => 'ios',
            'transaction_id' => $request->transaction_id,
            'receipt' => $request->receipt_data
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription activated successfully',
            'data' => [
                'subscription_id' => $clientSubscription->id,
                'expires_at' => $clientSubscription->end_at
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
            'subscription_id' => $subscription->id,
            'status' => 'active',
            'start_at' => now()->toDateString(),
            'end_at' => $subscription->type === 'year' ? now()->addYears($subscription->period)->toDateString() : now()->addMonths($subscription->period)->toDateString(),
            'platform' => 'android',
            'transaction_id' => $request->order_id,
            'receipt' => $request->purchase_token
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription activated successfully',
            'data' => [
                'subscription_id' => $clientSubscription->id,
                'expires_at' => $clientSubscription->end_at
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
                    'start_date' => $sub->start_at,
                    'end_date' => $sub->end_at,
                    'is_active' => $sub->isActive(),
                    'platform' => $sub->platform,
                    'price' => $sub->subscription->cost
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
            ->where('end_at', '>', now()->toDateString())
            ->with('subscription')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'has_active_subscription' => !!$activeSubscription,
                'subscription' => $activeSubscription ? [
                    'plan_name' => $activeSubscription->subscription->name_en,
                    'expires_at' => $activeSubscription->end_at,
                    'days_remaining' => now()->diffInDays($activeSubscription->end_at)
                ] : null
            ]
        ]);
    }
    
    // Cancel subscription
    public function cancelSubscription(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:client_subscriptions,id'
        ]);
        
        $client = $request->get('auth_user');
        $subscription = ClientSubscription::where('id', $request->subscription_id)
            ->where('client_id', $client->id)
            ->first();
            
        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found'
            ], 404);
        }
        
        $subscription->update(['status' => 'cancelled']);
        
        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully'
        ]);
    }
    
    // Restore subscription (for testing)
    public function restoreSubscription(Request $request)
    {
        $client = $request->get('auth_user');
        
        $subscription = ClientSubscription::where('client_id', $client->id)
            ->where('status', 'cancelled')
            ->where('end_at', '>', now()->toDateString())
            ->first();
            
        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No cancelled subscription found'
            ], 404);
        }
        
        $subscription->update(['status' => 'active']);
        
        return response()->json([
            'success' => true,
            'message' => 'Subscription restored successfully'
        ]);
    }

    // Apple receipt verification using App Store Server API
    private function verifyAppleReceipt($transactionId)
    {
        $jwt = $this->generateAppleJWT();
        if (!$jwt) {
            return false;
        }
        
        $baseUrl = config('app.env') === 'production' 
            ? 'https://api.storekit.itunes.apple.com'
            : 'https://api.storekit-sandbox.itunes.apple.com';
            
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "{$baseUrl}/inApps/v1/transactions/{$transactionId}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $jwt,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return isset($data['signedTransactionInfo']);
        }
        
        return false;
    }
    
    private function generateAppleJWT()
    {
        $keyId = config('services.apple.key_id');
        $teamId = config('services.apple.team_id');
        $bundleId = config('services.apple.bundle_id');
        $privateKeyPath = config('services.apple.private_key_path');
        
        if (!$keyId || !$teamId || !$bundleId || !file_exists($privateKeyPath)) {
            return null;
        }
        
        $privateKey = file_get_contents($privateKeyPath);
        
        $header = [
            'alg' => 'ES256',
            'kid' => $keyId,
            'typ' => 'JWT'
        ];
        
        $payload = [
            'iss' => $teamId,
            'iat' => time(),
            'exp' => time() + 3600,
            'aud' => 'appstoreconnect-v1',
            'bid' => $bundleId
        ];
        
        $headerEncoded = base64url_encode(json_encode($header));
        $payloadEncoded = base64url_encode(json_encode($payload));
        
        $signature = '';
        openssl_sign($headerEncoded . '.' . $payloadEncoded, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureEncoded = base64url_encode($signature);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }
    
    private function verifyWithAppStoreServerAPI($transactionId, $jwt)
    {
        $url = env('APP_ENV') === 'production'
            ? "https://api.storekit.itunes.apple.com/inApps/v1/transactions/{$transactionId}"
            : "https://api.storekit-sandbox.itunes.apple.com/inApps/v1/transactions/{$transactionId}";
            
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $jwt,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return isset($data['signedTransactionInfo']);
        }
        
        return null;
    }

    // Google Play purchase verification
    private function verifyGooglePlayPurchase($purchaseToken, $productId = null)
    {
        $packageName = env('GOOGLE_PLAY_PACKAGE_NAME'); // Add to .env
        $serviceAccountKey = env('GOOGLE_SERVICE_ACCOUNT_KEY'); // JSON key file path
        
        // Get access token
        $accessToken = $this->getGoogleAccessToken($serviceAccountKey);
        
        $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/subscriptions/{$productId}/tokens/{$purchaseToken}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
    
    private function getGoogleAccessToken($serviceAccountKey)
    {
        // Implement JWT token generation for Google Service Account
        // This is simplified - use Google Client Library in production
        return 'mock_access_token';
    }
}

// Helper function for base64url encoding
if (!function_exists('base64url_encode')) {
    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64url_decode')) {
    function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
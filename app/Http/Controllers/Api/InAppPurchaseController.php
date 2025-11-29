<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessSubscription;
use App\Models\ClientSubscription;
use Illuminate\Http\Request;
use Carbon\Carbon;

// Helper function for Apple JWT
if (!function_exists('base64url_encode')) {
    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

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
        try {
            $request->validate([
                'subscription_id' => 'required|exists:bussiness_subscriptions,id',
                'receipt_data' => 'required|string',
                'transaction_id' => 'required|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('iOS Purchase Validation Error', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 400);
        }

        $client = $request->get('auth_user');
        $subscription = BusinessSubscription::find($request->subscription_id);

        // Skip Apple verification in test mode
        $testMode = $request->header('x-test-mode') === 'true';
        
        // Debug logging
        \Log::info('iOS Purchase Debug', [
            'test_mode' => $testMode,
            'transaction_id' => $request->transaction_id,
            'headers' => $request->headers->all()
        ]);
        
        $isValid = $testMode ? true : $this->verifyAppleReceipt($request->transaction_id);
        
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
        \Log::info('Android Purchase Request', [
            'headers' => $request->headers->all(),
            'data' => $request->all()
        ]);
        
        try {
            $request->validate([
                'subscription_id' => 'required|exists:bussiness_subscriptions,id',
                'purchase_token' => 'required|string',
                'order_id' => 'required|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Android Purchase Validation Error', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 400);
        }

        $client = $request->get('auth_user');
        $subscription = BusinessSubscription::find($request->subscription_id);
        
        // Check if user already has an active subscription
        $existingSubscription = ClientSubscription::where('client_id', $client->id)
            ->where('status', 'active')
            ->where('end_at', '>', now()->toDateString())
            ->first();
            
        if ($existingSubscription) {
            return response()->json([
                'success' => false,
                'message' => 'User already has an active subscription',
                'data' => [
                    'existing_subscription' => [
                        'id' => $existingSubscription->id,
                        'expires_at' => $existingSubscription->end_at,
                        'days_remaining' => now()->diffInDays($existingSubscription->end_at)
                    ]
                ]
            ], 409);
        }

        // Skip Google Play verification in test mode
        $testMode = $request->header('x-test-mode') === 'true';
        
        // Debug logging
        \Log::info('Android Purchase Debug', [
            'test_mode' => $testMode,
            'order_id' => $request->order_id,
            'product_id' => $subscription->android_plan_id,
            'headers' => $request->headers->all()
        ]);
        
        if ($testMode) {
            $verificationResult = [
                'status' => 'success',
                'transaction_id' => $request->order_id,
                'payment_state' => 1,
                'test_mode' => true
            ];
        } else {
            try {
                // Verify purchase with Google Play
                $verificationResult = $this->verifyGooglePlayPurchase($request->purchase_token, $subscription->android_plan_id);
                
                if ($verificationResult['status'] !== 'success') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Purchase verification failed: ' . ($verificationResult['message'] ?? 'Unknown error')
                    ], 400);
                }
            } catch (\Exception $e) {
                \Log::error('Google Play verification error', ['error' => $e->getMessage()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Verification service unavailable: ' . $e->getMessage()
                ], 500);
            }
        }
        
        // Create subscription record
        $clientSubscription = ClientSubscription::create([
            'client_id' => $client->id,
            'subscription_id' => $subscription->id,
            'status' => 'active',
            'payment_status' => 'paid',
            'start_at' => now()->toDateString(),
            'end_at' => $subscription->type === 'year' ? now()->addYears($subscription->period)->toDateString() : now()->addMonths($subscription->period)->toDateString(),
            'platform' => 'android',
            'transaction_id' => $verificationResult['transaction_id'] ?? $request->order_id,
            'receipt' => $request->purchase_token,
            'product_id' => $subscription->android_plan_id,
            'response' => json_encode($verificationResult)
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
        
        // Always use sandbox for testing with mobile developers
        $baseUrl = 'https://api.storekit-sandbox.itunes.apple.com';
            
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
        $keyId = env('APPLE_KEY_ID');
        $teamId = env('APPLE_TEAM_ID');
        $bundleId = env('APPLE_BUNDLE_ID');
        $privateKeyPath = env('APPLE_PRIVATE_KEY_PATH');
        
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

    // Google Play purchase verification using REST API
    private function verifyGooglePlayPurchase($purchaseToken, $productId)
    {
        $packageName = env('GOOGLE_PLAY_PACKAGE_NAME');
        $serviceAccountPath = env('GOOGLE_SERVICE_ACCOUNT_KEY');
        
        if (!file_exists($serviceAccountPath)) {
            throw new \Exception('Google service account key file not found');
        }
        
        // Get access token
        $accessToken = $this->getGoogleAccessToken($serviceAccountPath);
        
        // Call Google Play API
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
        
        if ($httpCode !== 200) {
            \Log::error('Google Play API error', ['code' => $httpCode, 'response' => $response]);
            throw new \Exception('Google Play verification failed: HTTP ' . $httpCode);
        }
        
        $data = json_decode($response, true);
        
        if (!$data) {
            throw new \Exception('Invalid response from Google Play API');
        }
        
        // Check if subscription is valid
        if ($data['paymentState'] != 1) {
            throw new \Exception('Subscription payment not confirmed');
        }
        
        return [
            'status' => 'success',
            'transaction_id' => $data['orderId'] ?? 'unknown',
            'expiry_date' => isset($data['expiryTimeMillis']) ? date('Y-m-d H:i:s', $data['expiryTimeMillis'] / 1000) : null,
            'payment_state' => $data['paymentState'],
            'auto_renewing' => $data['autoRenewing'] ?? false,
            'start_time' => isset($data['startTimeMillis']) ? date('Y-m-d H:i:s', $data['startTimeMillis'] / 1000) : null
        ];
    }
    
    private function getGoogleAccessToken($serviceAccountPath)
    {
        $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
        
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];
        
        $payload = [
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/androidpublisher',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => time(),
            'exp' => time() + 3600
        ];
        
        $headerEncoded = base64url_encode(json_encode($header));
        $payloadEncoded = base64url_encode(json_encode($payload));
        
        $signature = '';
        openssl_sign($headerEncoded . '.' . $payloadEncoded, $signature, $serviceAccount['private_key'], OPENSSL_ALGO_SHA256);
        $signatureEncoded = base64url_encode($signature);
        
        $jwt = $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
        
        // Exchange JWT for access token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if (!isset($data['access_token'])) {
            throw new \Exception('Failed to get Google access token');
        }
        
        return $data['access_token'];
    }
}


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
            // Verify purchase with Google Play
            $verificationResult = $this->verifyGooglePlayPurchase($request->purchase_token, $subscription->android_plan_id);
            
            if ($verificationResult['status'] !== 'success') {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase verification failed: ' . ($verificationResult['message'] ?? 'Unknown error')
                ], 400);
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

    // Google Play purchase verification
    private function verifyGooglePlayPurchase($purchaseToken, $productId)
    {
        try {
            // Check if Google service account key exists
            $serviceAccountPath = env('GOOGLE_SERVICE_ACCOUNT_KEY');
            if (!file_exists($serviceAccountPath)) {
                \Log::error('Google service account key not found', ['path' => $serviceAccountPath]);
                return ['status' => 'error', 'message' => 'Service account key not found'];
            }

            $client = new \Google\Client();
            $client->setAuthConfig($serviceAccountPath);
            $client->addScope(\Google\Service\AndroidPublisher::ANDROIDPUBLISHER);
            
            $androidPublisher = new \Google\Service\AndroidPublisher($client);
            $packageName = env('GOOGLE_PLAY_PACKAGE_NAME');
            
            // Try subscription verification first
            try {
                $purchase = $androidPublisher->purchases_subscriptions->get(
                    $packageName,
                    $productId,
                    $purchaseToken
                );
                
                // Check payment state: 0=pending, 1=purchased, 2=free_trial, 3=pending_deferred
                $paymentState = $purchase->getPaymentState();
                if (in_array($paymentState, [1, 2])) { // purchased or free trial
                    return [
                        'status' => 'success',
                        'transaction_id' => $purchase->getOrderId(),
                        'expiry_date' => $purchase->getExpiryTimeMillis() ? date('Y-m-d H:i:s', $purchase->getExpiryTimeMillis() / 1000) : null,
                        'payment_state' => $paymentState,
                        'auto_renewing' => $purchase->getAutoRenewing(),
                        'type' => 'subscription'
                    ];
                }
                
                return ['status' => 'error', 'message' => 'Invalid subscription payment state: ' . $paymentState];
                
            } catch (\Google\Service\Exception $e) {
                // If subscription fails, try one-time product
                if ($e->getCode() === 404) {
                    try {
                        $purchase = $androidPublisher->purchases_products->get(
                            $packageName,
                            $productId,
                            $purchaseToken
                        );
                        
                        // Check purchase state: 0=purchased, 1=canceled
                        if ($purchase->getPurchaseState() === 0) {
                            return [
                                'status' => 'success',
                                'transaction_id' => $purchase->getOrderId(),
                                'purchase_time' => date('Y-m-d H:i:s', $purchase->getPurchaseTimeMillis() / 1000),
                                'purchase_state' => $purchase->getPurchaseState(),
                                'type' => 'product'
                            ];
                        }
                        
                        return ['status' => 'error', 'message' => 'Product purchase canceled or invalid'];
                        
                    } catch (\Google\Service\Exception $productException) {
                        \Log::error('Google Play product verification failed', [
                            'error' => $productException->getMessage(),
                            'code' => $productException->getCode()
                        ]);
                        return ['status' => 'error', 'message' => 'Purchase not found: ' . $productException->getMessage()];
                    }
                } else {
                    throw $e; // Re-throw if not a 404
                }
            }
            
        } catch (\Google\Service\Exception $e) {
            \Log::error('Google Play API error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'errors' => $e->getErrors()
            ]);
            return ['status' => 'error', 'message' => 'Google Play API error: ' . $e->getMessage()];
            
        } catch (\Exception $e) {
            \Log::error('Google Play verification failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'Verification failed: ' . $e->getMessage()];
        }
    }
}


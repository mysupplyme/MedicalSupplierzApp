<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessSubscription;
use App\Models\ClientSubscription;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    // Create test user for IAP testing
    public function createTestUser(Request $request)
    {
        $client = Client::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@medicalsupplierz.com',
            'password' => bcrypt('password123'),
            'mobile_number' => '1234567890',
            'country_code' => '1',
            'type' => 'doctor',
            'buyer_type' => 'doctor',
            'status' => 'active',
            'email_verified_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test user created',
            'data' => [
                'id' => $client->id,
                'email' => $client->email,
                'login_credentials' => [
                    'email' => 'test@medicalsupplierz.com',
                    'password' => 'password123'
                ]
            ]
        ]);
    }

    // Create test subscription plans
    public function createTestPlans(Request $request)
    {
        $plans = [
            [
                'name_en' => 'Basic Monthly',
                'name_ar' => 'الأساسية الشهرية',
                'description_en' => 'Basic monthly subscription',
                'description_ar' => 'اشتراك شهري أساسي',
                'period' => 1,
                'type' => 'month',
                'cost' => 9.99,
                'status' => 'active',
                'ios_plan_id' => 'com.medicalsupplierz.basic.monthly',
                'android_plan_id' => 'basic_monthly'
            ],
            [
                'name_en' => 'Premium Monthly',
                'name_ar' => 'المميزة الشهرية',
                'description_en' => 'Premium monthly subscription with all features',
                'description_ar' => 'اشتراك شهري مميز مع جميع الميزات',
                'period' => 1,
                'type' => 'month',
                'cost' => 19.99,
                'status' => 'active',
                'ios_plan_id' => 'com.medicalsupplierz.premium.monthly',
                'android_plan_id' => 'premium_monthly'
            ],
            [
                'name_en' => 'Premium Yearly',
                'name_ar' => 'المميزة السنوية',
                'description_en' => 'Premium yearly subscription (2 months free)',
                'description_ar' => 'اشتراك سنوي مميز (شهرين مجاناً)',
                'period' => 1,
                'type' => 'year',
                'cost' => 199.99,
                'status' => 'active',
                'ios_plan_id' => 'com.medicalsupplierz.premium.yearly',
                'android_plan_id' => 'premium_yearly'
            ]
        ];

        $created = [];
        foreach ($plans as $planData) {
            $plan = BusinessSubscription::create($planData);
            $created[] = $plan;
        }

        return response()->json([
            'success' => true,
            'message' => 'Test subscription plans created',
            'data' => $created
        ]);
    }

    // Simulate webhook notifications
    public function simulateAppleWebhook(Request $request)
    {
        $notificationType = $request->input('notification_type', 'SUBSCRIBED');
        $transactionId = $request->input('transaction_id', 'test_transaction_' . time());
        
        // Find subscription by transaction ID
        $subscription = ClientSubscription::where('transaction_id', $transactionId)->first();
        
        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found for transaction ID: ' . $transactionId
            ], 404);
        }

        switch ($notificationType) {
            case 'SUBSCRIBED':
            case 'DID_RENEW':
                $subscription->update([
                    'status' => 'active',
                    'end_at' => now()->addMonth()->toDateString()
                ]);
                break;
                
            case 'EXPIRED':
            case 'DID_CANCEL':
                $subscription->update(['status' => 'cancelled']);
                break;
                
            case 'DID_FAIL_TO_RENEW':
                $subscription->update(['status' => 'payment_failed']);
                break;
        }

        Log::info('Simulated Apple Webhook', [
            'notification_type' => $notificationType,
            'transaction_id' => $transactionId,
            'subscription_id' => $subscription->id
        ]);

        return response()->json([
            'success' => true,
            'message' => "Simulated {$notificationType} notification",
            'data' => [
                'subscription_id' => $subscription->id,
                'new_status' => $subscription->fresh()->status,
                'end_date' => $subscription->fresh()->end_at
            ]
        ]);
    }

    // Generate test JWT receipt
    public function generateTestJWT(Request $request)
    {
        $transactionId = $request->input('transaction_id', 'test_' . time());
        $productId = $request->input('product_id', 'com.medicalsupplierz.premium.monthly');
        $expiresInDays = $request->input('expires_in_days', 30);
        
        $header = [
            'alg' => 'ES256',
            'kid' => 'test_key_id',
            'typ' => 'JWT'
        ];
        
        $payload = [
            'transactionId' => $transactionId,
            'originalTransactionId' => $transactionId,
            'productId' => $productId,
            'bundleId' => env('APPLE_BUNDLE_ID', 'com.medicalsupplierz.app'),
            'expiresDate' => (time() + ($expiresInDays * 24 * 60 * 60)) * 1000, // milliseconds
            'purchaseDate' => time() * 1000,
            'environment' => 'Sandbox',
            'type' => 'Auto-Renewable Subscription'
        ];
        
        $headerEncoded = base64url_encode(json_encode($header));
        $payloadEncoded = base64url_encode(json_encode($payload));
        $signature = base64url_encode('mock_signature_for_testing');
        
        $jwt = "{$headerEncoded}.{$payloadEncoded}.{$signature}";
        
        return response()->json([
            'success' => true,
            'data' => [
                'jwt_receipt' => $jwt,
                'transaction_id' => $transactionId,
                'product_id' => $productId,
                'expires_date' => date('Y-m-d H:i:s', time() + ($expiresInDays * 24 * 60 * 60)),
                'decoded_payload' => $payload
            ]
        ]);
    }

    // Generate test traditional receipt
    public function generateTestReceipt(Request $request)
    {
        $transactionId = $request->input('transaction_id', 'test_' . time());
        $productId = $request->input('product_id', 'com.medicalsupplierz.premium.monthly');
        
        $receipt = [
            'receipt_type' => 'ProductionSandbox',
            'bundle_id' => env('APPLE_BUNDLE_ID', 'com.medicalsupplierz.app'),
            'transaction_id' => $transactionId,
            'original_transaction_id' => $transactionId,
            'product_id' => $productId,
            'purchase_date' => date('Y-m-d H:i:s'),
            'expires_date' => date('Y-m-d H:i:s', strtotime('+30 days'))
        ];
        
        $receiptData = base64_encode(json_encode($receipt));
        
        return response()->json([
            'success' => true,
            'data' => [
                'receipt_data' => $receiptData,
                'transaction_id' => $transactionId,
                'product_id' => $productId,
                'decoded_receipt' => $receipt
            ]
        ]);
    }

    // Reset test data
    public function resetTestData(Request $request)
    {
        // Delete test subscriptions
        ClientSubscription::where('transaction_id', 'like', 'test_%')->delete();
        
        // Delete test user
        Client::where('email', 'test@medicalsupplierz.com')->delete();
        
        // Delete test plans (optional)
        if ($request->input('delete_plans', false)) {
            BusinessSubscription::whereIn('ios_plan_id', [
                'com.medicalsupplierz.basic.monthly',
                'com.medicalsupplierz.premium.monthly',
                'com.medicalsupplierz.premium.yearly'
            ])->delete();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Test data reset successfully'
        ]);
    }

    // Get test status
    public function getTestStatus(Request $request)
    {
        $testUser = Client::where('email', 'test@medicalsupplierz.com')->first();
        $testPlans = BusinessSubscription::whereIn('ios_plan_id', [
            'com.medicalsupplierz.basic.monthly',
            'com.medicalsupplierz.premium.monthly',
            'com.medicalsupplierz.premium.yearly'
        ])->get();
        $testSubscriptions = ClientSubscription::where('transaction_id', 'like', 'test_%')->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'test_user_exists' => !!$testUser,
                'test_user_id' => $testUser?->id,
                'test_plans_count' => $testPlans->count(),
                'test_subscriptions_count' => $testSubscriptions->count(),
                'environment_variables' => [
                    'apple_bundle_id' => env('APPLE_BUNDLE_ID'),
                    'has_apple_shared_secret' => !empty(env('APPLE_SHARED_SECRET')),
                    'has_openai_key' => !empty(env('OPENAI_API_KEY'))
                ]
            ]
        ]);
    }
}

// Helper function for base64url encoding (if not already defined)
if (!function_exists('base64url_encode')) {
    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
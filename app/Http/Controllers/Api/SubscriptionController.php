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
            $query->where('buyer_type_id', 1); // 1 for doctor
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
    
    public function validateIOSPurchase(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:bussiness_subscriptions,id',
            'transaction_id' => 'required|string',
            'receipt_data' => 'required|string'
        ]);
        
        $receiptData = $request->receipt_data;
        
        // Check if it's a JWT (starts with eyJ)
        if (strpos($receiptData, 'eyJ') === 0) {
            return $this->validateJWTReceipt($receiptData, $request);
        } else {
            return $this->validateTraditionalReceipt($receiptData, $request);
        }
    }
    
    private function validateJWTReceipt($jwtToken, $request)
    {
        try {
            // Decode JWT without verification for now (you should verify signature in production)
            $parts = explode('.', $jwtToken);
            if (count($parts) !== 3) {
                throw new \Exception('Invalid JWT format');
            }
            
            $payload = json_decode(base64_decode(str_pad(strtr($parts[1], '-_', '+/'), strlen($parts[1]) % 4, '=', STR_PAD_RIGHT)), true);
            
            if (!$payload) {
                throw new \Exception('Invalid JWT payload');
            }
            
            // Validate bundle ID
            if ($payload['bundleId'] !== env('APPLE_BUNDLE_ID')) {
                throw new \Exception('Invalid bundle ID');
            }
            
            // Check if transaction is valid and not expired
            $expiresDate = $payload['expiresDate'] ?? 0;
            if ($expiresDate < time() * 1000) {
                throw new \Exception('Subscription expired');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Valid iOS purchase',
                'data' => [
                    'transaction_id' => $payload['transactionId'],
                    'product_id' => $payload['productId'],
                    'expires_date' => date('Y-m-d H:i:s', $expiresDate / 1000),
                    'environment' => $payload['environment'] ?? 'Production'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid receipt: ' . $e->getMessage()
            ], 400);
        }
    }
    
    private function validateTraditionalReceipt($receiptData, $request)
    {
        try {
            $url = env('APP_ENV') === 'production' 
                ? 'https://buy.itunes.apple.com/verifyReceipt'
                : 'https://sandbox.itunes.apple.com/verifyReceipt';
            
            $postData = json_encode([
                'receipt-data' => $receiptData,
                'password' => env('APPLE_SHARED_SECRET')
            ]);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $data = json_decode($response, true);
            
            if ($data['status'] !== 0) {
                throw new \Exception('Receipt validation failed: ' . ($data['status'] ?? 'Unknown error'));
            }
            
            $receipt = $data['receipt'];
            if ($receipt['bundle_id'] !== env('APPLE_BUNDLE_ID')) {
                throw new \Exception('Invalid bundle ID');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Valid iOS purchase',
                'data' => [
                    'transaction_id' => $receipt['transaction_id'] ?? null,
                    'product_id' => $receipt['product_id'] ?? null,
                    'purchase_date' => $receipt['purchase_date'] ?? null,
                    'environment' => env('APP_ENV') === 'production' ? 'Production' : 'Sandbox'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid receipt: ' . $e->getMessage()
            ], 400);
        }
    }
}
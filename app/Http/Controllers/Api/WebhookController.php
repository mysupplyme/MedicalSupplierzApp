<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class WebhookController extends Controller
{
    // Apple App Store Server Notifications V2
    public function appleWebhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Apple Webhook V2:', $payload);
        
        // Skip signature verification for testing
        if (config('app.env') !== 'local' && !$this->verifyAppleWebhookV2($request)) {
            Log::warning('Apple webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 400);
        }
        
        $signedPayload = $payload['signedPayload'] ?? '';
        $decodedPayload = $this->decodeAppleJWT($signedPayload);
        
        if (!$decodedPayload) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }
        
        $notificationType = $decodedPayload['notificationType'] ?? '';
        $data = $decodedPayload['data'] ?? [];
        
        switch ($notificationType) {
            case 'SUBSCRIBED':
                $this->handleAppleSubscribed($data);
                break;
            case 'DID_RENEW':
                $this->handleAppleRenewal($data);
                break;
            case 'EXPIRED':
            case 'DID_CANCEL':
                $this->handleAppleCancellation($data);
                break;
            case 'DID_FAIL_TO_RENEW':
                $this->handleAppleFailedRenewal($data);
                break;
            case 'GRACE_PERIOD_EXPIRED':
                $this->handleAppleGracePeriodExpired($data);
                break;
        }
        
        return response()->json(['status' => 'ok']);
    }
    
    // Google Play Developer Notifications
    public function googleWebhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Google Webhook:', $payload);
        
        $message = $payload['message'] ?? [];
        $data = json_decode(base64_decode($message['data'] ?? ''), true);
        
        $notificationType = $data['notificationType'] ?? 0;
        $subscriptionNotification = $data['subscriptionNotification'] ?? [];
        
        switch ($notificationType) {
            case 1: // SUBSCRIPTION_RECOVERED
            case 2: // SUBSCRIPTION_RENEWED
                $this->handleGoogleRenewal($subscriptionNotification);
                break;
            case 3: // SUBSCRIPTION_CANCELED
                $this->handleGoogleCancellation($subscriptionNotification);
                break;
            case 4: // SUBSCRIPTION_PURCHASED
                $this->handleGoogleInitialPurchase($subscriptionNotification);
                break;
        }
        
        return response()->json(['status' => 'ok']);
    }
    
    private function handleAppleRenewal($receiptData)
    {
        $transactionId = $receiptData['latest_receipt_info'][0]['original_transaction_id'] ?? '';
        
        $subscription = ClientSubscription::where('transaction_id', $transactionId)->first();
        if ($subscription) {
            $subscription->update([
                'status' => 'active',
                'end_at' => now()->addMonth()->toDateString()
            ]);
        }
    }
    
    private function handleAppleCancellation($receiptData)
    {
        $transactionId = $receiptData['latest_receipt_info'][0]['original_transaction_id'] ?? '';
        
        $subscription = ClientSubscription::where('transaction_id', $transactionId)->first();
        if ($subscription) {
            $subscription->update(['status' => 'cancelled']);
        }
    }
    
    private function handleGoogleRenewal($notification)
    {
        $purchaseToken = $notification['purchaseToken'] ?? '';
        
        $subscription = ClientSubscription::where('receipt', $purchaseToken)->first();
        if ($subscription) {
            $subscription->update([
                'status' => 'active',
                'end_at' => now()->addMonth()->toDateString()
            ]);
        }
    }
    
    private function handleGoogleCancellation($notification)
    {
        $purchaseToken = $notification['purchaseToken'] ?? '';
        
        $subscription = ClientSubscription::where('receipt', $purchaseToken)->first();
        if ($subscription) {
            $subscription->update(['status' => 'cancelled']);
        }
    }
    
    private function verifyAppleWebhookV2(Request $request)
    {
        $certUrl = $request->header('x-apple-cert-url');
        $signature = $request->header('x-apple-signature');
        
        if (!$certUrl || !$signature) {
            Log::warning('Apple webhook missing headers');
            return false;
        }
        
        // Verify certificate URL is from Apple
        if (!str_starts_with($certUrl, 'https://apps.apple.com/')) {
            Log::warning('Invalid Apple certificate URL', ['url' => $certUrl]);
            return false;
        }
        
        try {
            $cert = file_get_contents($certUrl);
            if (!$cert) {
                Log::error('Failed to download Apple certificate');
                return false;
            }
            
            $publicKey = openssl_pkey_get_public($cert);
            if (!$publicKey) {
                Log::error('Failed to extract public key from certificate');
                return false;
            }
            
            $payload = $request->getContent();
            $result = openssl_verify($payload, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256);
            
            return $result === 1;
        } catch (Exception $e) {
            Log::error('Apple webhook verification error', ['message' => $e->getMessage()]);
            return false;
        }
    }
    
    private function decodeAppleJWT($jwt)
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }
        
        $payload = json_decode(base64_decode($parts[1]), true);
        return $payload;
    }
    
    private function handleAppleSubscribed($data)
    {
        $transactionInfo = $this->decodeAppleJWT($data['signedTransactionInfo'] ?? '');
        if (!$transactionInfo) return;
        
        $originalTransactionId = $transactionInfo['originalTransactionId'] ?? '';
        $productId = $transactionInfo['productId'] ?? '';
        
        // Find or create subscription
        $subscription = ClientSubscription::where('transaction_id', $originalTransactionId)->first();
        if ($subscription) {
            $subscription->update(['status' => 'active']);
        }
    }
    
    private function handleAppleGracePeriodExpired($data)
    {
        $transactionInfo = $this->decodeAppleJWT($data['signedTransactionInfo'] ?? '');
        if (!$transactionInfo) return;
        
        $originalTransactionId = $transactionInfo['originalTransactionId'] ?? '';
        
        $subscription = ClientSubscription::where('transaction_id', $originalTransactionId)->first();
        if ($subscription) {
            $subscription->update(['status' => 'expired']);
        }
    }
}
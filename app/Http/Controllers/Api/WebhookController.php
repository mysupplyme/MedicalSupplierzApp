<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    // Apple App Store Server Notifications
    public function appleWebhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Apple Webhook:', $payload);
        
        if (!$this->verifyAppleWebhook($request)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }
        
        $notificationType = $payload['notification_type'] ?? '';
        $receiptData = $payload['unified_receipt'] ?? [];
        
        switch ($notificationType) {
            case 'INITIAL_BUY':
                $this->handleAppleInitialPurchase($receiptData);
                break;
            case 'DID_RENEW':
                $this->handleAppleRenewal($receiptData);
                break;
            case 'DID_CANCEL':
                $this->handleAppleCancellation($receiptData);
                break;
            case 'DID_FAIL_TO_RENEW':
                $this->handleAppleFailedRenewal($receiptData);
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
    
    private function verifyAppleWebhook(Request $request)
    {
        // Implement Apple webhook signature verification
        return true; // Simplified
    }
}
# In-App Purchase Testing Guide

## Quick Start

1. **Access Test Page**: Visit `/test-iap` in your browser
2. **Get Auth Token**: Login via `/api/login` first to get bearer token
3. **Test Purchase Flow**: Use the interactive testing dashboard

## Testing Steps

### 1. Setup Authentication
```bash
# Login to get auth token
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Use the returned token in Authorization header
```

### 2. Get Subscription Plans
```bash
curl -X GET http://localhost:8000/api/subscription-plans \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Test iOS Purchase (JWT Format)
```bash
curl -X POST http://localhost:8000/api/verify-ios-purchase \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "x-test-mode: true" \
  -d '{
    "subscription_id": 1,
    "transaction_id": "test_transaction_123",
    "receipt_data": "eyJhbGciOiJFUzI1NiIsImtpZCI6InRlc3QifQ.eyJ0cmFuc2FjdGlvbklkIjoidGVzdF90cmFuc2FjdGlvbl8xMjMiLCJwcm9kdWN0SWQiOiJjb20ubWVkaWNhbHN1cHBsaWVyei5wcmVtaXVtIiwiYnVuZGxlSWQiOiJjb20ubWVkaWNhbHN1cHBsaWVyei5hcHAiLCJleHBpcmVzRGF0ZSI6MTczNTY4MDAwMDAwMCwiZW52aXJvbm1lbnQiOiJTYW5kYm94In0.mock_signature"
  }'
```

### 4. Test iOS Purchase (Traditional Format)
```bash
curl -X POST http://localhost:8000/api/verify-ios-purchase \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "x-test-mode: true" \
  -d '{
    "subscription_id": 1,
    "transaction_id": "test_transaction_123",
    "receipt_data": "eyJyZWNlaXB0X3R5cGUiOiJQcm9kdWN0aW9uU2FuZGJveCIsImJ1bmRsZV9pZCI6ImNvbS5tZWRpY2Fsc3VwcGxpZXJ6LmFwcCIsInRyYW5zYWN0aW9uX2lkIjoidGVzdF90cmFuc2FjdGlvbl8xMjMiLCJwcm9kdWN0X2lkIjoiY29tLm1lZGljYWxzdXBwbGllcnoucHJlbWl1bSJ9"
  }'
```

### 5. Test Android Purchase
```bash
curl -X POST http://localhost:8000/api/verify-android-purchase \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "subscription_id": 1,
    "order_id": "test_order_123",
    "purchase_token": "eyJvcmRlcklkIjoidGVzdF9vcmRlcl8xMjMiLCJwYWNrYWdlTmFtZSI6ImNvbS5tZWRpY2Fsc3VwcGxpZXJ6LmFwcCIsInByb2R1Y3RJZCI6InByZW1pdW1fc3Vic2NyaXB0aW9uIiwicHVyY2hhc2VUaW1lIjoxNzM1NjgwMDAwMDAwLCJwdXJjaGFzZVN0YXRlIjowfQ=="
  }'
```

## Environment Variables Required

Add these to your `.env` file:

```env
# Apple Configuration
APPLE_BUNDLE_ID=com.medicalsupplierz.app
APPLE_SHARED_SECRET=your_shared_secret
APPLE_KEY_ID=your_key_id
APPLE_TEAM_ID=your_team_id
APPLE_PRIVATE_KEY_PATH=/path/to/AuthKey.p8

# Google Play Configuration
GOOGLE_PLAY_PACKAGE_NAME=com.medicalsupplierz.app
GOOGLE_SERVICE_ACCOUNT_KEY=/path/to/service-account.json
```

## Test Scenarios

### Scenario 1: New Subscription
1. User purchases subscription in mobile app
2. App sends receipt to `/api/verify-ios-purchase`
3. Server validates receipt and creates `ClientSubscription`
4. User gets access to premium features

### Scenario 2: Subscription Renewal
1. Apple/Google sends webhook notification
2. Server updates subscription end date
3. User continues to have access

### Scenario 3: Subscription Cancellation
1. User cancels in app store
2. Webhook updates subscription status to 'cancelled'
3. User loses access after current period ends

### Scenario 4: Failed Payment
1. Payment fails during renewal
2. Webhook notifies server
3. Grace period starts or subscription expires

## Mobile App Integration

### iOS (Swift)
```swift
// Purchase product
func purchaseProduct(productId: String) {
    // StoreKit purchase logic
    // On success, send receipt to server
    
    let receiptData = getReceiptData()
    let transactionId = transaction.transactionIdentifier
    
    verifyPurchase(
        subscriptionId: selectedPlan.id,
        transactionId: transactionId,
        receiptData: receiptData
    )
}

func verifyPurchase(subscriptionId: Int, transactionId: String, receiptData: String) {
    let url = "https://your-domain.com/api/verify-ios-purchase"
    // Send POST request with auth token
}
```

### Android (Kotlin)
```kotlin
// Purchase product
fun purchaseProduct(productId: String) {
    // Google Play Billing purchase logic
    // On success, send purchase token to server
    
    val purchaseToken = purchase.purchaseToken
    val orderId = purchase.orderId
    
    verifyPurchase(
        subscriptionId = selectedPlan.id,
        orderId = orderId,
        purchaseToken = purchaseToken
    )
}
```

## Debugging Tips

1. **Check Logs**: All requests are logged in Laravel logs
2. **Test Mode**: Use `x-test-mode: true` header to bypass Apple verification
3. **JWT Validation**: JWT receipts start with "eyJ"
4. **Database**: Check `client_subscriptions` table for created records
5. **Webhooks**: Test webhook endpoints with ngrok for local development

## Common Issues

1. **Invalid Bundle ID**: Ensure `APPLE_BUNDLE_ID` matches your app
2. **Receipt Format**: Check if receipt is JWT or traditional base64
3. **Authentication**: Verify bearer token is valid
4. **Subscription ID**: Ensure subscription exists in database
5. **Environment**: Use sandbox for testing, production for live app

## Production Checklist

- [ ] Valid Apple certificates and keys
- [ ] Google Service Account configured
- [ ] Webhook URLs registered with Apple/Google
- [ ] SSL certificates for webhook endpoints
- [ ] Error handling and logging
- [ ] Receipt signature verification enabled
- [ ] Database backups configured
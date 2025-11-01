# Mobile API Documentation - Medical Supplierz

Base URL: `https://medicalsupplierz.app/api`

## 🔐 Authentication

### Login
```http
POST /login
```
**Body:**
```json
{
  "email": "doctor@example.com",
  "password": "password123"
}
```
**Response:**
```json
{
  "success": true,
  "data": {
    "user": {...},
    "token": "simple-token-123"
  }
}
```

### Register
```http
POST /register
```
**Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "doctor@example.com",
  "password": "password123",
  "mobile_number": "1234567890",
  "country_id": 1,
  "country_code": "US",
  "phone_prefix": "+1",
  "job_title": "Cardiologist",
  "workplace": "City Hospital",
  "specialty_id": 1,
  "sub_specialty_id": 2,
  "nationality": 1,
  "residency": 1
}
```

### Forgot Password
```http
POST /forgot-password
```
**Body:**
```json
{
  "email": "doctor@example.com"
}
```

### Reset Password
```http
POST /reset-password
```
**Body:**
```json
{
  "token": "reset_token",
  "email": "doctor@example.com",
  "password": "newpassword123"
}
```

### Activate Account
```http
POST /activate-account
```
**Body:**
```json
{
  "email": "doctor@example.com",
  "activation_code": "123456"
}
```
**Response:**
```json
{
  "success": true,
  "message": "Account activated successfully! You can now login.",
  "data": {
    "email_verified": true,
    "verified_at": "2025-01-15T10:30:00.000000Z"
  }
}
```

### Resend Activation Code
```http
POST /resend-activation
```
**Body:**
```json
{
  "email": "doctor@example.com"
}
```

## 👤 Profile Management

### Get Profile
```http
GET /get_profile
Authorization: Bearer simple-token-123
```

### Get Profile by ID
```http
GET /profile/{id}
```

### Update Profile by ID
```http
PUT /profile/{id}
```
**Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "job_title": "Senior Cardiologist",
  "mobile_number": "1234567890",
  "country_code": 1,
  "workplace": "New Hospital",
  "specialty_id": 1,
  "sub_specialty_id": 2,
  "residency": 1,
  "nationality": 1,
  "register_number": "REG123456",
  "currency_id": 1,
  "language": "en"
}
```

### Change Password
```http
POST /change_password
Authorization: Bearer simple-token-123
```
**Body:**
```json
{
  "current_password": "oldpassword",
  "new_password": "newpassword123"
}
```

## 📚 Common Data APIs

### Get Specialties
```http
GET /common/specialties
```

### Get Sub-Specialties
```http
GET /common/sub-specialties?specialty_id=1
```

### Get Countries (Residencies)
```http
GET /common/get_residencies
```

### Get Nationalities
```http
GET /common/get_nationalities
```

### Get Country Codes
```http
GET /common/get_country_codes
```

### Get Currencies
```http
GET /common/get_currencies
```

### Get Categories
```http
GET /common/get_categories
```

### Get Conferences
```http
GET /common/conferences
```

## 📅 Events Management

### Get Events
```http
GET /events
Authorization: Bearer simple-token-123
```

### Get Event Details
```http
GET /events/{eventId}/details
```

### Create Event
```http
POST /events
Authorization: Bearer simple-token-123
```

### Update Event
```http
PUT /events/{eventId}
Authorization: Bearer simple-token-123
```

### Delete Event
```http
DELETE /events/{eventId}
Authorization: Bearer simple-token-123
```

### Register for Event
```http
POST /events/{eventId}/register
Authorization: Bearer simple-token-123
```

## 💳 Subscription & In-App Purchases

### Get Subscription Plans
```http
GET /subscription-plans
Authorization: Bearer simple-token-123
```

### Verify iOS Purchase
```http
POST /verify-ios-purchase
Authorization: Bearer simple-token-123
```
**Body:**
```json
{
  "subscription_id": 1,
  "receipt_data": "base64_receipt_data",
  "transaction_id": "ios_transaction_id"
}
```

### Verify Android Purchase
```http
POST /verify-android-purchase
Authorization: Bearer simple-token-123
```
**Body:**
```json
{
  "subscription_id": 1,
  "purchase_token": "google_purchase_token",
  "order_id": "google_order_id"
}
```

### Get My Subscriptions
```http
GET /my-subscriptions
Authorization: Bearer simple-token-123
```

### Check Subscription Status
```http
GET /subscription-status
Authorization: Bearer simple-token-123
```

### Cancel Subscription
```http
POST /cancel-subscription
Authorization: Bearer simple-token-123
```
**Body:**
```json
{
  "subscription_id": 1
}
```

### Restore Subscription
```http
POST /restore-subscription
Authorization: Bearer simple-token-123
```

## 🛍️ Products API

### Get Products
```http
GET /v1/products
```

### Get Product Details
```http
GET /v1/products/{id}
```

### Test Products API
```http
GET /v1/products/test
```

## 📋 Categories & Lists

### Get Categories List
```http
GET /lists/categories
```

### Get Categories Tree
```http
GET /lists/categories/tree
```

### Get Category Products
```http
GET /common/category-products/{categoryId}
```

### Get Product Suppliers
```http
GET /common/product-suppliers/{productId}
```

## 📞 Contact & Support

### Send Contact Message
```http
POST /contact
```
**Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "subject": "Inquiry",
  "message": "Hello, I need help with..."
}
```

## 📄 Terms & Conditions

### Get Terms & Conditions
```http
GET /terms-conditions
```

## 🔧 Admin APIs (No Auth Required)

### Doctor Management
```http
GET /admin/doctors
GET /admin/doctors/{id}
PUT /admin/doctors/{id}/status
DELETE /admin/doctors/{id}
```

### Subscription Management
```http
GET /admin/subscriptions
GET /admin/subscriptions/{id}
PUT /admin/subscriptions/{id}/status
PUT /admin/subscriptions/{id}/extend
GET /admin/subscription-stats
```

### Package Management
```http
GET /admin/packages
POST /admin/packages
GET /admin/packages/{id}
PUT /admin/packages/{id}
DELETE /admin/packages/{id}
```

## 🔗 Webhooks

### Apple Webhook
```http
POST /webhooks/apple
```

### Google Webhook
```http
POST /webhooks/google
```

### WhatsApp Webhook
```http
GET|POST /webhooks/whatsapp
GET /webhooks/whatsapp/test
```

## 📱 Headers Required

For all authenticated requests:
```
Authorization: Bearer simple-token-{user_id}
Content-Type: application/json
Accept: application/json
```

For products API:
```
Content-Type: application/json
Accept: application/json
```

## 🚨 Error Responses

```json
{
  "success": false,
  "message": "Error description",
  "errors": {...}
}
```

## 📊 Response Format

Success responses:
```json
{
  "success": true,
  "data": {...},
  "message": "Optional message"
}
```

## 🔒 Authentication Notes

- Token format: `simple-token-{user_id}`
- No token expiration implemented
- Middleware: `simple.auth` for protected routes
- User type: `buyer` (doctors)

## 📱 Mobile App Integration

### iOS Configuration
- Bundle ID: `com.ms.medicalsupplierzlite`
- Test mode header: `x-test-mode: true`

### Android Configuration
- Package Name: `com.ms.medicalsupplierzlite`

---

*Last Updated: January 2025*
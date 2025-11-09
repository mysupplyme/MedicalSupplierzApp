<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In-App Purchase Testing - MedicalSupplierz</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; background: #f8fafc; padding: 2rem; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        h1, h2 { color: #1e40af; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; }
        button { background: #10b981; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; margin-right: 1rem; margin-bottom: 0.5rem; }
        button:hover { background: #059669; }
        .btn-secondary { background: #6b7280; }
        .btn-secondary:hover { background: #4b5563; }
        .response { background: #f3f4f6; padding: 1rem; border-radius: 4px; margin-top: 1rem; white-space: pre-wrap; font-family: monospace; }
        .success { background: #d1fae5; color: #065f46; }
        .error { background: #fee2e2; color: #991b1b; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        .endpoint { background: #f9fafb; padding: 1rem; border-left: 4px solid #10b981; margin-bottom: 1rem; }
        .method { background: #1e40af; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>In-App Purchase Testing Dashboard</h1>
        
        <!-- Subscription Plans -->
        <div class="card">
            <h2>1. Get Subscription Plans</h2>
            <div class="endpoint">
                <span class="method">GET</span> /api/subscription-plans
            </div>
            <button onclick="getPlans()">Get Available Plans</button>
            <div id="plans-response" class="response"></div>
        </div>

        <div class="grid">
            <!-- iOS Testing -->
            <div class="card">
                <h2>2. iOS Purchase Testing</h2>
                
                <div class="form-group">
                    <label>Subscription ID:</label>
                    <select id="ios-subscription-id">
                        <option value="">Select Plan</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Transaction ID:</label>
                    <input type="text" id="ios-transaction-id" placeholder="1000000123456789" value="test_transaction_123">
                </div>
                
                <div class="form-group">
                    <label>Receipt Type:</label>
                    <select id="ios-receipt-type" onchange="updateReceiptData()">
                        <option value="jwt">JWT Receipt (New)</option>
                        <option value="traditional">Traditional Receipt</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Receipt Data:</label>
                    <textarea id="ios-receipt-data" rows="4" placeholder="Receipt data will be generated"></textarea>
                </div>
                
                <button onclick="generateTestReceipt()">Generate Test Receipt</button>
                <button onclick="testIOSPurchase()" class="btn-secondary">Test iOS Purchase</button>
                
                <div id="ios-response" class="response"></div>
            </div>

            <!-- Android Testing -->
            <div class="card">
                <h2>3. Android Purchase Testing</h2>
                
                <div class="form-group">
                    <label>Subscription ID:</label>
                    <select id="android-subscription-id">
                        <option value="">Select Plan</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Order ID:</label>
                    <input type="text" id="android-order-id" placeholder="GPA.1234-5678-9012-34567" value="test_order_123">
                </div>
                
                <div class="form-group">
                    <label>Purchase Token:</label>
                    <textarea id="android-purchase-token" rows="3" placeholder="Purchase token from Google Play"></textarea>
                </div>
                
                <button onclick="generateAndroidToken()">Generate Test Token</button>
                <button onclick="testAndroidPurchase()" class="btn-secondary">Test Android Purchase</button>
                
                <div id="android-response" class="response"></div>
            </div>
        </div>

        <!-- Subscription Status -->
        <div class="card">
            <h2>4. Subscription Management</h2>
            
            <button onclick="getMySubscriptions()">Get My Subscriptions</button>
            <button onclick="checkSubscriptionStatus()" class="btn-secondary">Check Status</button>
            
            <div class="form-group" style="margin-top: 1rem;">
                <label>Subscription ID to Cancel:</label>
                <input type="text" id="cancel-subscription-id" placeholder="Enter subscription ID">
                <button onclick="cancelSubscription()">Cancel Subscription</button>
            </div>
            
            <div id="subscription-response" class="response"></div>
        </div>

        <!-- API Endpoints Reference -->
        <div class="card">
            <h2>5. API Endpoints Reference</h2>
            
            <div class="endpoint">
                <span class="method">GET</span> /api/subscription-plans - Get available plans
            </div>
            
            <div class="endpoint">
                <span class="method">POST</span> /api/verify-ios-purchase - Verify iOS purchase
                <br><small>Headers: Authorization: Bearer {token}, x-test-mode: true</small>
            </div>
            
            <div class="endpoint">
                <span class="method">POST</span> /api/verify-android-purchase - Verify Android purchase
                <br><small>Headers: Authorization: Bearer {token}</small>
            </div>
            
            <div class="endpoint">
                <span class="method">GET</span> /api/my-subscriptions - Get user subscriptions
            </div>
            
            <div class="endpoint">
                <span class="method">GET</span> /api/subscription-status - Check subscription status
            </div>
        </div>
    </div>

    <script>
        const API_BASE = '/api';
        let authToken = 'test_token_123'; // You'll need to get this from login

        // Get subscription plans
        async function getPlans() {
            try {
                const response = await fetch(`${API_BASE}/subscription-plans`, {
                    headers: { 'Authorization': `Bearer ${authToken}` }
                });
                const data = await response.json();
                
                document.getElementById('plans-response').textContent = JSON.stringify(data, null, 2);
                document.getElementById('plans-response').className = 'response success';
                
                // Populate dropdowns
                const plans = data.data || [];
                const iosSelect = document.getElementById('ios-subscription-id');
                const androidSelect = document.getElementById('android-subscription-id');
                
                iosSelect.innerHTML = '<option value="">Select Plan</option>';
                androidSelect.innerHTML = '<option value="">Select Plan</option>';
                
                plans.forEach(plan => {
                    iosSelect.innerHTML += `<option value="${plan.id}">${plan.title} - $${plan.price}</option>`;
                    androidSelect.innerHTML += `<option value="${plan.id}">${plan.title} - $${plan.price}</option>`;
                });
                
            } catch (error) {
                document.getElementById('plans-response').textContent = 'Error: ' + error.message;
                document.getElementById('plans-response').className = 'response error';
            }
        }

        // Generate test receipt data
        function generateTestReceipt() {
            const type = document.getElementById('ios-receipt-type').value;
            const transactionId = document.getElementById('ios-transaction-id').value;
            
            if (type === 'jwt') {
                // Generate mock JWT
                const header = btoa(JSON.stringify({alg: "ES256", kid: "test"}));
                const payload = btoa(JSON.stringify({
                    transactionId: transactionId,
                    productId: "com.medicalsupplierz.premium",
                    bundleId: "com.medicalsupplierz.app",
                    expiresDate: Date.now() + (30 * 24 * 60 * 60 * 1000), // 30 days
                    environment: "Sandbox"
                }));
                const signature = btoa("mock_signature");
                
                document.getElementById('ios-receipt-data').value = `eyJ${header}.${payload}.${signature}`;
            } else {
                // Generate mock traditional receipt
                document.getElementById('ios-receipt-data').value = btoa(JSON.stringify({
                    receipt_type: "ProductionSandbox",
                    bundle_id: "com.medicalsupplierz.app",
                    transaction_id: transactionId,
                    product_id: "com.medicalsupplierz.premium"
                }));
            }
        }

        // Generate Android test token
        function generateAndroidToken() {
            const token = btoa(JSON.stringify({
                orderId: document.getElementById('android-order-id').value,
                packageName: "com.medicalsupplierz.app",
                productId: "premium_subscription",
                purchaseTime: Date.now(),
                purchaseState: 0
            }));
            document.getElementById('android-purchase-token').value = token;
        }

        // Test iOS purchase
        async function testIOSPurchase() {
            const data = {
                subscription_id: document.getElementById('ios-subscription-id').value,
                transaction_id: document.getElementById('ios-transaction-id').value,
                receipt_data: document.getElementById('ios-receipt-data').value
            };
            
            try {
                const response = await fetch(`${API_BASE}/verify-ios-purchase`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${authToken}`,
                        'x-test-mode': 'true'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                document.getElementById('ios-response').textContent = JSON.stringify(result, null, 2);
                document.getElementById('ios-response').className = response.ok ? 'response success' : 'response error';
                
            } catch (error) {
                document.getElementById('ios-response').textContent = 'Error: ' + error.message;
                document.getElementById('ios-response').className = 'response error';
            }
        }

        // Test Android purchase
        async function testAndroidPurchase() {
            const data = {
                subscription_id: document.getElementById('android-subscription-id').value,
                order_id: document.getElementById('android-order-id').value,
                purchase_token: document.getElementById('android-purchase-token').value
            };
            
            try {
                const response = await fetch(`${API_BASE}/verify-android-purchase`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${authToken}`
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                document.getElementById('android-response').textContent = JSON.stringify(result, null, 2);
                document.getElementById('android-response').className = response.ok ? 'response success' : 'response error';
                
            } catch (error) {
                document.getElementById('android-response').textContent = 'Error: ' + error.message;
                document.getElementById('android-response').className = 'response error';
            }
        }

        // Get user subscriptions
        async function getMySubscriptions() {
            try {
                const response = await fetch(`${API_BASE}/my-subscriptions`, {
                    headers: { 'Authorization': `Bearer ${authToken}` }
                });
                const data = await response.json();
                
                document.getElementById('subscription-response').textContent = JSON.stringify(data, null, 2);
                document.getElementById('subscription-response').className = 'response success';
                
            } catch (error) {
                document.getElementById('subscription-response').textContent = 'Error: ' + error.message;
                document.getElementById('subscription-response').className = 'response error';
            }
        }

        // Check subscription status
        async function checkSubscriptionStatus() {
            try {
                const response = await fetch(`${API_BASE}/subscription-status`, {
                    headers: { 'Authorization': `Bearer ${authToken}` }
                });
                const data = await response.json();
                
                document.getElementById('subscription-response').textContent = JSON.stringify(data, null, 2);
                document.getElementById('subscription-response').className = 'response success';
                
            } catch (error) {
                document.getElementById('subscription-response').textContent = 'Error: ' + error.message;
                document.getElementById('subscription-response').className = 'response error';
            }
        }

        // Cancel subscription
        async function cancelSubscription() {
            const subscriptionId = document.getElementById('cancel-subscription-id').value;
            if (!subscriptionId) {
                alert('Please enter subscription ID');
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/cancel-subscription`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${authToken}`
                    },
                    body: JSON.stringify({ subscription_id: subscriptionId })
                });
                
                const data = await response.json();
                document.getElementById('subscription-response').textContent = JSON.stringify(data, null, 2);
                document.getElementById('subscription-response').className = response.ok ? 'response success' : 'response error';
                
            } catch (error) {
                document.getElementById('subscription-response').textContent = 'Error: ' + error.message;
                document.getElementById('subscription-response').className = 'response error';
            }
        }

        // Update receipt data when type changes
        function updateReceiptData() {
            document.getElementById('ios-receipt-data').value = '';
        }

        // Load plans on page load
        window.onload = function() {
            getPlans();
            generateTestReceipt();
            generateAndroidToken();
        };
    </script>
</body>
</html>
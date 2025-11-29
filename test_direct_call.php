<?php

require_once 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';

try {
    // Test direct method call
    $controller = new \App\Http\Controllers\Api\InAppPurchaseController();
    
    // Create mock request
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'subscription_id' => 4,
        'purchase_token' => 'test_token',
        'order_id' => 'test_order'
    ]);
    $request->headers->set('x-test-mode', 'true');
    $request->headers->set('Authorization', 'Bearer simple-token-3164');
    
    // Mock auth user
    $mockClient = new stdClass();
    $mockClient->id = 3164;
    $request->merge(['auth_user' => $mockClient]);
    
    echo "Calling verifyAndroidPurchase method directly...\n";
    $response = $controller->verifyAndroidPurchase($request);
    echo "Response: " . $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
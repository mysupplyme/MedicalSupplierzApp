<?php

// Simple test script to debug the Android endpoint
require_once 'vendor/autoload.php';

use Illuminate\Http\Request;

// Create a mock request
$data = [
    'subscription_id' => 4,
    'purchase_token' => 'test_token',
    'order_id' => 'test_order'
];

echo "Testing Android Purchase Endpoint\n";
echo "Data: " . json_encode($data) . "\n";

// Test the route exists
$routes = file_get_contents('routes/api.php');
if (strpos($routes, 'verify-android-purchase') !== false) {
    echo "✓ Route exists in api.php\n";
} else {
    echo "✗ Route NOT found in api.php\n";
}

// Check if controller exists
if (file_exists('app/Http/Controllers/Api/InAppPurchaseController.php')) {
    echo "✓ Controller exists\n";
} else {
    echo "✗ Controller NOT found\n";
}

echo "\nDone.\n";
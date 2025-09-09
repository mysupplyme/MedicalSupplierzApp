<?php
// Simple test to verify the API works
require_once 'vendor/autoload.php';

use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;

echo "Testing ProductController...\n";

try {
    $controller = new ProductController();
    echo "Controller instantiated successfully\n";
    
    // Create a mock request
    $request = Request::create('/api/v1/products', 'GET', [
        'count' => 2,
        'page' => 1
    ]);
    
    $response = $controller->index($request);
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content: " . substr($response->getContent(), 0, 200) . "...\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
#!/usr/bin/env php
<?php

/**
 * Test script for /api/v1/products endpoint
 * Usage: php test_products_api.php [base_url]
 */

$baseUrl = $argv[1] ?? 'http://localhost:8000';

function testEndpoint($url, $description) {
    echo "\n=== Testing: $description ===\n";
    echo "URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Accept-Language: en',
        'Country-Id: 1',
        'Currency-Id: 1',
        'platform: web'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ Error: $error\n";
        return false;
    }
    
    echo "Status: $httpCode\n";
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "✅ Success\n";
            if (isset($data['data']['pagination']['total'])) {
                echo "Total products: " . $data['data']['pagination']['total'] . "\n";
            }
            return true;
        } else {
            echo "❌ Invalid response format\n";
            echo substr($response, 0, 200) . "...\n";
        }
    } else {
        echo "❌ HTTP Error: $httpCode\n";
        echo substr($response, 0, 200) . "...\n";
    }
    
    return false;
}

echo "Testing Products API Endpoints\n";
echo "Base URL: $baseUrl\n";

// Test cases
$tests = [
    // Basic test
    "$baseUrl/api/v1/products/test" => "Parameter test endpoint",
    
    // Basic products list
    "$baseUrl/api/v1/products" => "Basic products list",
    
    // With pagination
    "$baseUrl/api/v1/products?page=1&limit=5" => "With pagination (page=1, limit=5)",
    
    // With category filter
    "$baseUrl/api/v1/products?category_id=1" => "With category filter (category_id=1)",
    
    // With search
    "$baseUrl/api/v1/products?search=medical" => "With search (search=medical)",
    
    // With price sorting
    "$baseUrl/api/v1/products?sort_price=asc" => "With price sorting (sort_price=asc)",
    
    // Combined parameters
    "$baseUrl/api/v1/products?page=1&limit=3&search=test&sort_price=desc" => "Combined parameters",
    
    // Legacy count parameter
    "$baseUrl/api/v1/products?count=5" => "Legacy count parameter"
];

$passed = 0;
$total = count($tests);

foreach ($tests as $url => $description) {
    if (testEndpoint($url, $description)) {
        $passed++;
    }
    sleep(1); // Avoid overwhelming the server
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test Results: $passed/$total tests passed\n";

if ($passed === $total) {
    echo "🎉 All tests passed!\n";
    exit(0);
} else {
    echo "❌ Some tests failed\n";
    exit(1);
}
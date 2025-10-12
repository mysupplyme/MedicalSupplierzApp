#!/usr/bin/env php
<?php

/**
 * Test script for /api/login endpoint
 * Usage: php test_login_api.php [base_url]
 */

$baseUrl = $argv[1] ?? 'http://localhost:8000';

function testLogin($url, $email, $password, $description) {
    echo "\n=== Testing: $description ===\n";
    echo "Email: $email\n";
    
    $data = json_encode([
        'email' => $email,
        'password' => $password
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
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
    
    $data = json_decode($response, true);
    if ($data) {
        echo "Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        
        if ($httpCode === 200 && isset($data['success']) && $data['success']) {
            echo "✅ Login successful\n";
            if (isset($data['data']['token'])) {
                echo "Token: " . $data['data']['token'] . "\n";
            }
            return $data['data']['token'] ?? true;
        } else {
            echo "❌ Login failed\n";
        }
    } else {
        echo "❌ Invalid JSON response\n";
        echo "Raw response: " . substr($response, 0, 200) . "\n";
    }
    
    return false;
}

function testProtectedEndpoint($url, $token, $description) {
    echo "\n=== Testing: $description ===\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Status: $httpCode\n";
    
    if ($httpCode === 200) {
        echo "✅ Protected endpoint accessible\n";
        return true;
    } else {
        echo "❌ Protected endpoint failed\n";
        echo "Response: " . substr($response, 0, 200) . "\n";
    }
    
    return false;
}

echo "Testing Login API\n";
echo "Base URL: $baseUrl\n";

$loginUrl = "$baseUrl/api/login";

// Test cases
$tests = [
    // Valid login (you'll need to create a test user)
    ['test@example.com', 'password123', 'Valid credentials'],
    
    // Invalid email
    ['invalid@example.com', 'password123', 'Invalid email'],
    
    // Invalid password
    ['test@example.com', 'wrongpassword', 'Invalid password'],
    
    // Missing fields
    ['', '', 'Empty credentials'],
];

$token = null;

foreach ($tests as [$email, $password, $description]) {
    $result = testLogin($loginUrl, $email, $password, $description);
    if ($result && !$token) {
        $token = $result; // Save first successful token
    }
    sleep(1);
}

// Test protected endpoint if we got a token
if ($token) {
    testProtectedEndpoint("$baseUrl/api/get_profile", $token, "Protected profile endpoint");
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Login API Test Complete\n";

// Instructions for creating test user
echo "\nTo create a test user, run:\n";
echo "INSERT INTO clients (uuid, type, first_name, last_name, email, password, buyer_type, is_buyer, status, created_at, updated_at) VALUES\n";
echo "(UUID(), 'buyer', 'Test', 'User', 'test@example.com', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'doctor', 1, 1, NOW(), NOW());\n";
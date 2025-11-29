<?php

// Test with real Google Play purchase data
$url = 'https://medicalsupplierz.app/api/verify-android-purchase';

$data = [
    'subscription_id' => 4,
    'purchase_token' => 'jjjaolaghfnpnchdejefabgh.AO-J1OzMefIb1KN0Q1gk5ElZTylBq-9CmpqV0JCut-ER3qU8z_PJHySWWu4pmLrMNmaUugEzQfiOk6JrZU3gBV0vNuNe6xsMoZBP3CW_Mex8lpv0Gg4bNbw',
    'order_id' => 'GPA.3328-2832-8072-27767'
];

$headers = [
    'Content-Type: application/json',
    'x-test-mode: true',
    'Authorization: Bearer simple-token-3164'
];

echo "Testing Android Purchase Verification\n";
echo "URL: $url\n";
echo "Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
echo "Headers: " . implode(', ', $headers) . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "=== RESPONSE ===\n";
echo "HTTP Code: $httpCode\n";
echo "Response Body: $response\n";

if ($error) {
    echo "cURL Error: $error\n";
}

// Try to decode JSON response
if ($response) {
    $decoded = json_decode($response, true);
    if ($decoded) {
        echo "\nDecoded Response:\n";
        print_r($decoded);
    }
}
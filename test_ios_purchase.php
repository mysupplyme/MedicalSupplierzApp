<?php

// Test script for /api/verify-ios-purchase endpoint

$baseUrl = 'http://localhost:8000'; // Adjust if your server runs on different port
$endpoint = '/api/verify-ios-purchase';

// Test data provided by user
$testData = [
    'subscription_id' => 4,
    'transaction_id' => '2000001042162856',
    'receipt_data' => 'eyJhbGciOiJFUzI1NiIsIng1YyI6WyJNSUlFTVRDQ0E3YWdBd0lCQWdJUVI4S0h6ZG41NTRaL1VvcmFkTng5dHpBS0JnZ3Foa2pPUFFRREF6QjFNVVF3UWdZRFZRUURERHRCY0hCc1pTQlhiM0pzWkhkcFpHVWdSR1YyWld4dmNHVnlJRkpsYkdGMGFXOXVjeUJEWlhKMGFXWnBZMkYwYVc5dUlFRjFkR2h2Y21sMGVURUxNQWtHQTFVRUN3d0NSell4RXpBUkJnTlZCQW9NQ2tGd2NHeGxJRWx1WXk0eEN6QUpCZ05WQkFZVEFsVlRNQjRYRFRJMU1Ea3hPVEU1TkRRMU1Wb1hEVEkzTVRBeE16RTNORGN5TTFvd2daSXhRREErQmdOVkJBTU1OMUJ5YjJRZ1JVTkRJRTFoWXlCQmNIQWdVM1J2Y21VZ1lXNWtJR2xVZFc1bGN5QlRkRzl5WlNCU1pXTmxhWEIwSUZOcFoyNXBibWN4TERBcUJnTlZCQXNNSTBGd2NHeGxJRmR2Y214a2QybGtaU0JFWlhabGJHOXdaWElnVW1Wc1lYUnBiMjV6TVJNd0VRWURWUVFLREFwQmNIQnNaU0JKYm1NdU1Rc3dDUVlEVlFRR0V3SlZVekJaTUJNR0J5cUdTTTQ5QWdFR0NDcUdTTTQ5QXdFSEEwSUFCTm5WdmhjdjdpVCs3RXg1dEJNQmdyUXNwSHpJc1hSaTBZeGZlazdsdjh3RW1qL2JIaVd0TndKcWMyQm9IenNRaUVqUDdLRklJS2c0WTh5MC9ueW51QW1qZ2dJSU1JSUNCREFNQmdOVkhSTUJBZjhFQWpBQU1COEdBMVVkSXdRWU1CYUFGRDh2bENOUjAxREptaWc5N2JCODVjK2xrR0taTUhBR0NDc0dBUVVGQndFQkJHUXdZakF0QmdnckJnRUZCUWN3QW9ZaGFIUjBjRG92TDJObGNuUnpMbUZ3Y0d4bExtTnZiUzkzZDJSeVp6WXVaR1Z5TURFR0NDc0dBUVVGQnpBQmhpVm9kSFJ3T2k4dmIyTnpjQzVoY0hCc1pTNWpiMjB2YjJOemNEQXpMWGQzWkhKbk5qQXlNSUlCSGdZRFZSMGdCSUlCRlRDQ0FSRXdnZ0VOQmdvcWhraUc5Mk5rQlFZQk1JSCtNSUhEQmdnckJnRUZCUWNDQWpDQnRneUJzMUpsYkdsaGJtTmxJRzl1SUhSb2FYTWdZMlZ5ZEdsbWFXTmhkR1VnWW5rZ1lXNTVJSEJoY25SNUlHRnpjM1Z0WlhNZ1lXTmpaWEIwWVc1alpTQnZaaUIwYUdVZ2RHaGxiaUJoY0hCc2FXTmhZbXhsSUhOMFlXNWtZWEprSUhSbGNtMXpJR0Z1WkNCamIyNWthWFJwYjI1eklHOW1JSFZ6WlN3Z1kyVnlkR2xtYVdOaGRHVWdjRzlzYVdONUlHRnVaQ0JqWlhKMGFXWnBZMkYwYVc5dUlIQnlZV04wYVdObElITjBZWFJsYldWdWRITXVNRFlHQ0NzR0FRVUZCd0lCRmlwb2RIUndPaTh2ZDNkM0xtRndjR3hsTG1OdmJTOWpaWEowYVdacFkyRjBaV0YxZEdodmNtbDBlUzh3SFFZRFZSME9CQllFRklGaW9HNHdNTVZBMWt1OXpKbUdOUEFWbjNlcU1BNEdBMVVkRHdFQi93UUVBd0lIZ0RBUUJnb3Foa2lHOTJOa0Jnc0JCQUlGQURBS0JnZ3Foa2pPUFFRREF3TnBBREJtQWpFQStxWG5SRUM3aFhJV1ZMc0x4em5qUnBJelBmN1ZIejlWL0NUbTgrTEpsclFlcG5tY1B2R0xOY1g2WFBubGNnTEFBakVBNUlqTlpLZ2c1cFE3OWtuRjRJYlRYZEt2OHZ1dElETVhEbWpQVlQzZEd2RnRzR1J3WE95d1Iya1pDZFNyZmVvdCIsIk1JSURGakNDQXB5Z0F3SUJBZ0lVSXNHaFJ3cDBjMm52VTRZU3ljYWZQVGp6Yk5jd0NnWUlLb1pJemowRUF3TXdaekViTUJrR0ExVUVBd3dTUVhCd2JHVWdVbTl2ZENCRFFTQXRJRWN6TVNZd0pBWURWUVFMREIxQmNIQnNaU0JEWlhKMGFXWnBZMkYwYVc5dUlFRjFkR2h2Y21sMGVURVRNQkVHQTFVRUNnd0tRWEJ3YkdVZ1NXNWpMakVMTUFrR0ExVUVCaE1DVlZNd0hoY05NakV3TXpFM01qQXpOekV3V2hjTk16WXdNekU1TURBd01EQXdXakIxTVVRd1FnWURWUVFERER0QmNIQnNaU0JYYjNKc1pIZHBaR1VnUkdWMlpXeHZjR1Z5SUZKbGJHRjBhVzl1Y3lCRFpYSjBhV1pwWTJGMGFXOXVJRUYxZEdodmNtbDBlVEVMTUFrR0ExVUVDd3dDUnpZeEV6QVJCZ05WQkFvTUNrRndjR3hsSUVsdVl5NHhDekFKQmdOVkJBWVRBbFZUTUhZd0VBWUhLb1pJemowQ0FRWUZLNEVFQUNJRFlnQUVic1FLQzk0UHJsV21aWG5YZ3R4emRWSkw4VDBTR1luZ0RSR3BuZ24zTjZQVDhKTUViN0ZEaTRiQm1QaENuWjMvc3E2UEYvY0djS1hXc0w1dk90ZVJoeUo0NXgzQVNQN2NPQithYW85MGZjcHhTdi9FWkZibmlBYk5nWkdoSWhwSW80SDZNSUgzTUJJR0ExVWRFd0VCL3dRSU1BWUJBZjhDQVFBd0h3WURWUjBqQkJnd0ZvQVV1N0Rlb1ZnemlKcWtpcG5ldnIzcnI5ckxKS3N3UmdZSUt3WUJCUVVIQVFFRU9qQTRNRFlHQ0NzR0FRVUZCekFCaGlwb2RIUndPaTh2YjJOemNDNWhjSEJzWlM1amIyMHZiMk56Y0RBekxXRndjR3hsY205dmRHTmhaek13TndZRFZSMGZCREF3TGpBc29DcWdLSVltYUhSMGNEb3ZMMk55YkM1aGNIQnNaUzVqYjIwdllYQndiR1Z5YjI5MFkyRm5NeTVqY213d0hRWURWUjBPQkJZRUZEOHZsQ05SMDFESm1pZzk3YkI4NWMrbGtHS1pNQTRHQTFVZER3RUIvd1FFQXdJQkJqQVFCZ29xaGtpRzkyTmtCZ0lCQkFJRkFEQUtCZ2dxaGtqT1BRUURBd05vQURCbEFqQkFYaFNxNUl5S29nTUNQdHc0OTBCYUI2NzdDYUVHSlh1ZlFCL0VxWkdkNkNTamlDdE9udU1UYlhWWG14eGN4ZmtDTVFEVFNQeGFyWlh2TnJreFUzVGtVTUkzM3l6dkZWVlJUNHd4V0pDOTk0T3NkY1o0K1JHTnNZRHlSNWdtZHIwbkRHZz0iLCJNSUlDUXpDQ0FjbWdBd0lCQWdJSUxjWDhpTkxGUzVVd0NnWUlLb1pJemowRUF3TXdaekViTUJrR0ExVUVBd3dTUVhCd2JHVWdVbTl2ZENCRFFTQXRJRWN6TVNZd0pBWURWUVFMREIxQmNIQnNaU0JEWlhKMGFXWnBZMkYwYVc5dUlFRjFkR2h2Y21sMGVURVRNQkVHQTFVRUNnd0tRWEJ3YkdVZ1NXNWpMakVMTUFrR0ExVUVCaE1DVlZNd0hoY05NVFF3TkRNd01UZ3hPVEEyV2hjTk16a3dORE13TVRneE9UQTJXakJuTVJzd0dRWURWUVFEREJKQmNIQnNaU0JTYjI5MElFTkJJQzBnUnpNeEpqQWtCZ05WQkFzTUhVRndjR3hsSUVObGNuUnBabWxqWVhScGIyNGdRWFYwYUc5eWFYUjVNUk13RVFZRFZRUUtEQXBCY0hCc1pTQkpibU11TVFzd0NRWURWUVFHRXdKVlV6QjJNQkFHQnlxR1NNNDlBZ0VHQlN1QkJBQWlBMklBQkpqcEx6MUFjcVR0a3lKeWdSTWMzUkNWOGNXalRuSGNGQmJaRHVXbUJTcDNaSHRmVGpqVHV4eEV0WC8xSDdZeVlsM0o2WVJiVHpCUEVWb0EvVmhZREtYMUR5eE5CMGNUZGRxWGw1ZHZNVnp0SzUxN0lEdll1VlRaWHBta09sRUtNYU5DTUVBd0hRWURWUjBPQkJZRUZMdXczcUZZTTRpYXBJcVozcjY5NjYvYXl5U3JNQThHQTFVZEV3RUIvd1FGTUFNQkFmOHdEZ1lEVlIwUEFRSC9CQVFEQWdFR01Bb0dDQ3FHU000OUJBTURBMmdBTUdVQ01RQ0Q2Y0hFRmw0YVhUUVkyZTN2OUd3T0FFWkx1Tit5UmhIRkQvM21lb3locG12T3dnUFVuUFdUeG5TNGF0K3FJeFVDTUcxbWloREsxQTNVVDgyTlF6NjBpbU9sTTI3amJkb1h0MlFmeUZNbStZaGlkRGtMRjF2TFVhZ002QmdENTZLeUtBPT0iXX0.eyJ0cmFuc2FjdGlvbklkIjoiMjAwMDAwMTA0MjE2Mjg1NiIsIm9yaWdpbmFsVHJhbnNhY3Rpb25JZCI6IjIwMDAwMDA5OTgzMTA4NzIiLCJ3ZWJPcmRlckxpbmVJdGVtSWQiOiIyMDAwMDAwMTEwNzkwNjEzIiwiYnVuZGxlSWQiOiJjb20ubXMubWVkaWNhbHN1cHBsaWVyemxpdGUiLCJwcm9kdWN0SWQiOiJtc2xpdGVfZG9jdG9yX21vbnRobHkiLCJzdWJzY3JpcHRpb25Hcm91cElkZW50aWZpZXIiOiIyMTc1MjA2MSIsInB1cmNoYXNlRGF0ZSI6MTc2MTM4OTU5NDAwMCwib3JpZ2luYWxQdXJjaGFzZURhdGUiOjE3NTY4NzAyODEwMDAsImV4cGlyZXNEYXRlIjoxNzYxMzkwNDk0MDAwLCJxdWFudGl0eSI6MSwidHlwZSI6IkF1dG8tUmVuZXdhYmxlIFN1YnNjcmlwdGlvbiIsImRldmljZVZlcmlmaWNhdGlvbiI6IkxYVlh2WDZJV0Ixa2hiSm4wSFpIN09pK0EzVHRBVXB6NGNlcW1qdGUxWFY1UDNWVjN2cjJndXRaTEY0UjZvTkYiLCJkZXZpY2VWZXJpZmljYXRpb25Ob25jZSI6IjE1MzYxMDYxLTU2MTgtNGExMi05M2Q1LWVhMDFiNDQwNGRiYSIsImluQXBwT3duZXJzaGlwVHlwZSI6IlBVUkNIQVNFRCIsInNpZ25lZERhdGUiOjE3NjEzODk1OTQzMjAsImVudmlyb25tZW50IjoiU2FuZGJveCIsInRyYW5zYWN0aW9uUmVhc29uIjoiUFVSQ0hBU0UiLCJzdG9yZWZyb250IjoiVVNBIiwic3RvcmVmcm9udElkIjoiMTQzNDQxIiwicHJpY2UiOjk5MCwiY3VycmVuY3kiOiJVU0QiLCJhcHBUcmFuc2FjdGlvbklkIjoiNzA0ODE3Mjc4MTU2NDU1MDMyIn0.APvcXz_7GQPPqJ-PTGzbAVrKtdRY-LIi63PKEY9k248mP0WfdYBQmRIFx-4WI4sKPvWRWgkUyTbFX5Y6fkwibQ'
];

// You need a valid client ID for authentication
// Let's first try to get a client ID
echo "Getting a test client ID...\n";

try {
    // Get first client ID from database
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=api', 'root', '123456');
    $stmt = $pdo->query('SELECT id FROM clients LIMIT 1');
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$client) {
        echo "No clients found in database. Creating a test client...\n";
        
        // Create a test client
        $stmt = $pdo->prepare('INSERT INTO clients (email, phone, created_at, updated_at) VALUES (?, ?, NOW(), NOW())');
        $stmt->execute(['test@example.com', '1234567890']);
        $clientId = $pdo->lastInsertId();
    } else {
        $clientId = $client['id'];
    }
    
    echo "Using client ID: $clientId\n";
    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    echo "Using default client ID: 1\n";
    $clientId = 1;
}

// Create authentication token
$authToken = 'simple-token-' . $clientId;

// Test the endpoint
echo "\nTesting /api/verify-ios-purchase endpoint...\n";
echo "URL: $baseUrl$endpoint\n";
echo "Auth Token: $authToken\n";
echo "Data: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $authToken,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "=== RESPONSE ===\n";
echo "HTTP Code: $httpCode\n";

if ($error) {
    echo "cURL Error: $error\n";
} else {
    echo "Response Body:\n";
    $responseData = json_decode($response, true);
    if ($responseData) {
        echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo $response . "\n";
    }
}

echo "\n=== JWT PAYLOAD ANALYSIS ===\n";
// Decode the JWT to show what's inside
$jwtToken = $testData['receipt_data'];
$parts = explode('.', $jwtToken);
if (count($parts) === 3) {
    $payload = json_decode(base64_decode(str_pad(strtr($parts[1], '-_', '+/'), strlen($parts[1]) % 4, '=', STR_PAD_RIGHT)), true);
    if ($payload) {
        echo "JWT Payload:\n";
        echo json_encode($payload, JSON_PRETTY_PRINT) . "\n";
        
        echo "\nKey Information:\n";
        echo "- Transaction ID: " . ($payload['transactionId'] ?? 'N/A') . "\n";
        echo "- Product ID: " . ($payload['productId'] ?? 'N/A') . "\n";
        echo "- Bundle ID: " . ($payload['bundleId'] ?? 'N/A') . "\n";
        echo "- Environment: " . ($payload['environment'] ?? 'N/A') . "\n";
        echo "- Purchase Date: " . (isset($payload['purchaseDate']) ? date('Y-m-d H:i:s', $payload['purchaseDate'] / 1000) : 'N/A') . "\n";
        echo "- Expires Date: " . (isset($payload['expiresDate']) ? date('Y-m-d H:i:s', $payload['expiresDate'] / 1000) : 'N/A') . "\n";
        echo "- Is Expired: " . (isset($payload['expiresDate']) && $payload['expiresDate'] < time() * 1000 ? 'YES' : 'NO') . "\n";
    }
}

?>
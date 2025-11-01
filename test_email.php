<?php
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test activation email
$testClient = (object) [
    'first_name' => 'Test',
    'last_name' => 'User',
    'email' => 'test@example.com'
];

$activationCode = '123456';

echo "Testing activation email...\n";

try {
    Mail::send('emails.activation', [
        'client' => $testClient,
        'activation_code' => $activationCode
    ], function ($message) use ($testClient) {
        $message->to($testClient->email)
                ->subject('TEST: Activate Your Medical Supplierz Account');
    });
    
    echo "Activation email sent successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Done.\n";
?>
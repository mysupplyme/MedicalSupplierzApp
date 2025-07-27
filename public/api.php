<?php
header('Content-Type: application/json');

// Simple API endpoints for mobile app
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/api.php/common/specialties' && $method === 'GET') {
    echo json_encode([
        'success' => true,
        'data' => [
            ['id' => 1, 'name' => 'Cardiology'],
            ['id' => 2, 'name' => 'Neurology'],
            ['id' => 3, 'name' => 'Orthopedics']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
}
?>
<?php
header('Content-Type: application/json');
session_start();
require_once '../handlers/upload_handler.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to view upload history']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$uploadHandler = new UploadHandler();
$history = $uploadHandler->getUploadHistory($_SESSION['user_id']);

if (is_array($history)) {
    echo json_encode([
        'success' => true,
        'data' => $history
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve upload history'
    ]);
}
?> 
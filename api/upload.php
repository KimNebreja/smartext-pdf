<?php
header('Content-Type: application/json');
session_start();
require_once '../handlers/upload_handler.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to upload files']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$uploadHandler = new UploadHandler();

// Get processing options
$options = [
    'check_grammar' => isset($_POST['check_grammar']) ? filter_var($_POST['check_grammar'], FILTER_VALIDATE_BOOLEAN) : true,
    'check_spelling' => isset($_POST['check_spelling']) ? filter_var($_POST['check_spelling'], FILTER_VALIDATE_BOOLEAN) : true
];

// Get custom name if provided
$customName = isset($_POST['custom_name']) ? sanitizeInput($_POST['custom_name']) : null;

// Handle file upload
if (!isset($_FILES['pdf_file'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['pdf_file'];

// Validate file type
$allowedTypes = ['application/pdf'];
if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Only PDF files are allowed']);
    exit;
}

// Validate file size (max 10MB)
$maxFileSize = 10 * 1024 * 1024; // 10MB in bytes
if ($file['size'] > $maxFileSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB limit']);
    exit;
}

// Process upload
$result = $uploadHandler->uploadFile($file, $_SESSION['user_id'], $customName, $options);

if ($result['success']) {
    // Update status to processing
    $uploadHandler->updateUploadStatus($result['upload_id'], 'processing');
    
    // TODO: Add your PDF processing logic here
    // This could be a separate background process or queue
    
    echo json_encode([
        'success' => true,
        'message' => 'File uploaded successfully and queued for processing',
        'upload_id' => $result['upload_id']
    ]);
} else {
    http_response_code(500);
    echo json_encode($result);
}
?> 
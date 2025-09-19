<?php
header('Content-Type: application/json');
require_once '../config/database.php';
session_start();

// Validate and decode the 'processed_id' from the query
if (!isset($_GET['processed_id'])) {
    echo json_encode([
        'message' => 'error',
        'info' => [],
        'error' => 'No processed_id provided.'
    ]);
    exit;
}

$encodedId = trim($_GET['processed_id']);
$decodedId = base64_decode($encodedId, true); // strict mode

// Validate decoding
if ($decodedId === false) {
    echo json_encode([
        'message' => 'error',
        'info' => [],
        'error' => 'Invalid base64-encoded ID.'
    ]);
    exit;
}

/**
 * Fetch process information from the database
 */
function getProcessInformation($id)
{
    try {
        $conn = getDBConnection();
        if (!$conn) {
            throw new Exception("Database connection failed.");
        }

        $stmt = $conn->prepare("SELECT * FROM processed_files a inner join uploads b on a.upload_id = b.upload_id WHERE a.processed_id = :processed_id");
        $stmt->bindParam(':processed_id', $id);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'data' => $results
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}


$response = getProcessInformation($decodedId);
if ($response['success']) {
    echo json_encode([
        'message' => 'success',
        'info' => $response['data']
    ]);
} else {
    echo json_encode([
        'message' => 'error',
        'info' => [],
        'error' => $response['error'] ?? 'Unknown error'
    ]);
}

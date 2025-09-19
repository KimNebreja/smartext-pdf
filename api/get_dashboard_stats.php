<?php
require_once '../config/database.php';
session_start();
$user_id = trim($_SESSION['user_id']);

function getProcessedUploadsByUser($userId)
{
    try {
        $conn = getDBConnection();
        if (!$conn) {
            throw new Exception("Database connection failed.");
        }

        $stmt = $conn->prepare("
            SELECT 
                a.original_filename,
                a.file_path AS original_file,
                b.processed_file_path AS proofread_file,
                a.file_size,
                b.processing_time,
                b.error_count,
                a.upload_date
            FROM uploads a
            INNER JOIN processed_files b ON a.upload_id = b.upload_id
            WHERE a.user_id = :user_id
        ");

        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [
            'success' => true,
            'data' => $results
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

$results = getProcessedUploadsByUser($user_id);

// Calculate statistics
$totalProcessed = count($results['data']);
$totalProcessingTime = 0;
$totalErrors = 0;

foreach ($results['data'] as $row) {
    $totalProcessingTime += (int)$row['processing_time'];
    $totalErrors += isset($row['error_count']) ? (int)$row['error_count'] : 0;
}

$averageTime = $totalProcessed > 0 ? round($totalProcessingTime / $totalProcessed, 2) : 0;
$averageErrorCount = $totalProcessed > 0 ? round($totalErrors / $totalProcessed, 2) : 0;

echo json_encode([
    'message' => $results['success'] ? 'success' : 'error',
    'result' => $results['data'],
    'stats' => [
        'totalProcessed' => $totalProcessed,
        'averageErrorCount' => $averageErrorCount,
        'averageTime' => $averageTime . 's'
    ]
]);
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
                a.upload_id,
                b.processed_id,
                a.original_filename,
                b.processed_file_path AS proofread_file,
                a.file_size,
                b.processing_time,
                a.upload_date,
                b.proof_data_path as json_data
            FROM uploads a
            INNER JOIN processed_files b ON a.upload_id = b.upload_id
            WHERE a.user_id = :user_id and b.processed_file_path <> '' and b.proof_data_path <> '' ORDER BY a.upload_id DESC
        ");

        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as &$row) {
            if (isset($row['upload_date'])) {
                $date = new DateTime($row['upload_date']);
                $row['upload_date'] = $date->format('Y-m-d h:i:s a');
            }
        }

        foreach ($results as &$row) {
            if (isset($row['processing_time'])) {
                $seconds = (int)$row['processing_time'];
                $minutes = intdiv($seconds, 60);
                $remainingSeconds = $seconds % 60;

                if ($minutes > 0) {
                    $row['processing_time'] = "{$minutes}m {$remainingSeconds}s";
                } else {
                    $row['processing_time'] = "{$seconds}s";
                }
            }
        }


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

if ($results['success']) {
    $message = 'success';
} else {
    $message = 'error';
}


echo json_encode([
    'message' => $message,
    'result' => $results['data'],
    'resultmsg' =>  $results['message']
]);

<?php
header('Content-Type: application/json');
require_once '../config/database.php';
session_start();

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {


        function getProcessedUploadsByUser($upload_id)
        {
            try {
                $conn = getDBConnection();
                if (!$conn) {
                    throw new Exception("Database connection failed.");
                }

                $stmt = $conn->prepare("
                    SELECT 
                        b.processed_id
                    FROM uploads a
                    INNER JOIN processed_files b ON a.upload_id = b.upload_id
                    WHERE a.upload_id = :upload_id 
                    AND b.processed_file_path <> '' 
                    AND b.proof_data_path <> '' 
                    ORDER BY a.upload_id DESC
                    LIMIT 1
                ");

                $stmt->bindParam(':upload_id', $upload_id);
                $stmt->execute();

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    return [
                        'success' => true,
                        'data' => $result['processed_id']
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 0
                    ];
                }

            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }


        // Read incoming JSON data
        $data = json_decode(file_get_contents("php://input"), true);

        // Extract parameters from the received data
        $uploadId = $data['id'] ?? '';
        $pdf = $data['pdf'] ?? '';
        $json = $data['json'] ?? '';
        $time = $data['time'] ?? '';
        $improvements = $data['improvements'] ?? 0;

        $dataArray = [
            'uploadId' => $uploadId,
            'pdf' => $pdf,
            'json' => $json,
            'time' => $time,
            'improvements' => $improvements
        ];

        // Validate the received data
        if (empty($uploadId) || empty($pdf) || empty($json) || empty($time)) {
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        // Assuming the `uploadDate` is coming from the session or another source.
        $uploadDate = date('Y-m-d H:i:s'); // Or get from session or another source.


        $conn = getDBConnection();
        if (!$conn) {
            unlink($tempPath);
            throw new Exception("Failed to connect to the database.");
        }

        try {
            $conn->beginTransaction();

            // Prepare the SQL statement for updating the database
            $stmt = $conn->prepare("UPDATE processed_files SET
                                        processed_file_path = :processed_file_path,
                                        proof_data_path = :proof_data_path,
                                        error_count = :error_count,
                                        processing_time = :processing_time,
                                        processed_date = :processed_date
                                    WHERE upload_id = :upload_id");

            // Bind the parameters
            $stmt->bindParam(':upload_id', $uploadId);
            $stmt->bindParam(':processed_file_path', $pdf);
            $stmt->bindParam(':proof_data_path', $json);
            $stmt->bindParam(':error_count', $improvements);
            $stmt->bindParam(':processing_time', $time);
            $stmt->bindParam(':processed_date', $uploadDate);

            // Execute the query
            $stmt->execute();

            // Commit the transaction if everything goes fine
            $conn->commit();

            $processing_id = getProcessedUploadsByUser($uploadId);

            if ($processing_id['success']) {
                echo json_encode([
                    'message' => 'success',
                    'process_id' => $processing_id['data']
                ]);
            } else {
                echo json_encode([
                    'message' => 'error',
                    'error' => $processing_id['message']
                ]);
            }

        } catch (Exception $e) {
            // Rollback in case of failure
            $conn->rollBack();
            echo json_encode(['error' => 'Failed to process file: ' . $e->getMessage()]);
        }
    } else {
        // If the request method is not POST, return an error
        echo json_encode(['error' => 'Invalid request method']);
    }

} catch (\Throwable $th) {
    echo json_encode(['error' => 'Request error due to: '. $th]);
}
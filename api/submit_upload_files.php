<?php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');
require_once '../config/database.php';
session_start();

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User not authenticated.");
    }

    $userId = $_SESSION['user_id'];

    if (!isset($_FILES['file'])) {
        throw new Exception("No file uploaded.");
    }

    $file = $_FILES['file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload error code: " . $file['error']);
    }

    $uploadDir = '../original_pdfs/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        throw new Exception("Failed to create upload directory.");
    }

    $originalFilename = basename($file['name']);

    // Extract filename without extension and sanitize it
    $customName = pathinfo($originalFilename, PATHINFO_FILENAME);
    $customName = preg_replace('/[^a-zA-Z0-9_\-]/', '', $customName);

    $uniqueID = uniqid();
    // Generate a unique filename for storage (but not for DB custom_name)
    $uniqueName = $uniqueID . '_' . $customName . '.pdf';
    $generatedUniqueFilename = $uniqueID . '_' . $customName;
    $finalPath = $uploadDir . $uniqueName;
    $tempPath = $uploadDir . 'temp_' . $uniqueName;

    // Step 1: Move file to temp path
    if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
        throw new Exception("Failed to move uploaded file.");
    }

    $fileSize = $file['size'];
    $uploadDate = date('Y-m-d H:i:s');
    $processedDate = null;

    $conn = getDBConnection();
    if (!$conn) {
        unlink($tempPath);
        throw new Exception("Failed to connect to the database.");
    }

    // Step 2: Insert into `uploads` table
    $conn->beginTransaction();
    $stmt = $conn->prepare("INSERT INTO uploads (
        user_id, original_filename, custom_name, file_size, file_path, upload_date, processed_date
    ) VALUES (
        :user_id, :original_filename, :custom_name, :file_size, :file_path, :upload_date, :processed_date
    )");

    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':original_filename', $originalFilename);
    $stmt->bindParam(':custom_name', $customName); // ✅ Insert name WITHOUT .pdf
    $stmt->bindParam(':file_size', $fileSize);
    $stmt->bindParam(':file_path', $finalPath);
    $stmt->bindParam(':upload_date', $uploadDate);
    $stmt->bindParam(':processed_date', $processedDate);

    $stmt->execute();

    // Get the uploaded file's ID
    $uploadId = $conn->lastInsertId();

    // Commit the file upload record
    $conn->commit();

    // Step 3: Rename file from temp to final location
    if (!rename($tempPath, $finalPath)) {
        // Rollback if renaming fails
        $conn->rollBack();
        unlink($tempPath);
        throw new Exception("Failed to finalize file upload.");
    }

    $error = '';
    // Simulate the external API call (e.g., FastAPI)
    $returnFromFastAPI = [
        "json_filename" => '',
        "final_pdf_filename" => '',
        "elapsed_time_seconds" => 0,
        "total_errors" => 0
    ];

    // Step 4: Insert into `processed_files` table (after processing)
    try {
        $conn->beginTransaction();
        $stmt = $conn->prepare("INSERT INTO processed_files (
            upload_id, processed_file_path, proof_data_path, error_count, processed_date, processing_time
        ) VALUES (
            :upload_id, :processed_file_path, :proof_data_path, :error_count, :processed_date, :processing_time
        )");

        $stmt->bindParam(':upload_id', $uploadId);
        $stmt->bindParam(':processed_file_path', $returnFromFastAPI['final_pdf_filename']);
        $stmt->bindParam(':proof_data_path', $returnFromFastAPI['json_filename']);
        $stmt->bindParam(':error_count', $returnFromFastAPI['total_errors']);
        $stmt->bindParam(':processed_date', $uploadDate);
        $stmt->bindParam(':processing_time', $returnFromFastAPI['elapsed_time_seconds']);

        $stmt->execute();

        // Commit the processed files record
        $conn->commit();
    } catch (Exception $e) {
        // Rollback if the API call or DB insert fails
        $conn->rollBack();
        throw new Exception("Failed to process file in API. " . $e->getMessage());
    }

    echo json_encode([
        'success' => true,
        'message' => 'File uploaded and processed successfully',
        'upload_id' => $uploadId,
        'custom_name' => $customName,
        'file_size' => $fileSize,
        'upload_date' => $uploadDate,
        'uploadedId' => $uploadId,
        'filenamewithid' => $finalPath,
        'generatedfilename' => $generatedUniqueFilename
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

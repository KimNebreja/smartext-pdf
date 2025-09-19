<?php
session_start();
require_once '../config/database.php';

class UploadHandler
{
    private $conn;
    private $uploadDir = '../uploads/';
    private $processedDir = '../processed/';

    public function __construct()
    {
        $this->conn = getDBConnection();
        $this->createDirectories();
    }

    private function createDirectories()
    {
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
        if (!file_exists($this->processedDir)) {
            mkdir($this->processedDir, 0777, true);
        }
    }

    public function uploadFile($file, $userId, $customName = null, $options = array())
    {
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $userId) {
                return array(
                    'success' => false,
                    'message' => 'Unauthorized access'
                );
            }

            // Validate file
            if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                return array(
                    'success' => false,
                    'message' => 'No file uploaded'
                );
            }

            // Generate unique filename
            $originalFilename = $file['name'];
            $fileExtension = pathinfo($originalFilename, PATHINFO_EXTENSION);
            $uniqueFilename = uniqid() . '_' . time() . '.' . $fileExtension;
            $filePath = $this->uploadDir . $uniqueFilename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return array(
                    'success' => false,
                    'message' => 'Failed to save file'
                );
            }

            // Insert into database
            $stmt = $this->conn->prepare("
                INSERT INTO uploads (user_id, original_filename, custom_name, file_size, file_path)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $originalFilename,
                $customName,
                $file['size'],
                $filePath
            ]);
            $uploadId = $this->conn->lastInsertId();

            // Save processing options
            $stmt = $this->conn->prepare("
                INSERT INTO processing_options (upload_id, check_grammar, check_spelling)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $uploadId,
                $options['check_grammar'] ?? true,
                $options['check_spelling'] ?? true
            ]);

            return array(
                'success' => true,
                'message' => 'File uploaded successfully',
                'upload_id' => $uploadId
            );
        } catch (PDOException $e) {
            return handleDBError($e);
        }
    }

    public function getUploadHistory($userId)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT u.*, po.check_grammar, po.check_spelling, pf.error_count, pf.suggestion_count
                FROM uploads u
                LEFT JOIN processing_options po ON u.upload_id = po.upload_id
                LEFT JOIN processed_files pf ON u.upload_id = pf.upload_id
                WHERE u.user_id = ?
                ORDER BY u.upload_date DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return handleDBError($e);
        }
    }

    public function updateUploadStatus($uploadId, $status)
    {
        try {
            $stmt = $this->conn->prepare("
                UPDATE uploads
                SET status = ?,
                    processed_date = CASE WHEN ? = 'completed' THEN CURRENT_TIMESTAMP ELSE NULL END
                WHERE upload_id = ?
            ");
            $stmt->execute([$status, $status, $uploadId]);
            return array(
                'success' => true,
                'message' => 'Status updated successfully'
            );
        } catch (PDOException $e) {
            return handleDBError($e);
        }
    }

    public function saveProcessedFile($uploadId, $processedFilePath, $errorCount, $suggestionCount, $processingTime)
    {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO processed_files (upload_id, processed_file_path, error_count, suggestion_count, processing_time)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $uploadId,
                $processedFilePath,
                $errorCount,
                $suggestionCount,
                $processingTime
            ]);
            return array(
                'success' => true,
                'message' => 'Processed file saved successfully'
            );
        } catch (PDOException $e) {
            return handleDBError($e);
        }
    }
}

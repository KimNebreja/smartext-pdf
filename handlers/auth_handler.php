<?php
session_start();
require_once '../config/database.php';

class AuthHandler {
    private $conn;

    public function __construct() {
        $this->conn = getDBConnection();
    }

    public function register($fullName, $email, $password) {
        try {
            // Check if email already exists
            $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                return array(
                    'success' => false,
                    'message' => 'Email already registered'
                );
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $this->conn->prepare("
                INSERT INTO users (full_name, email, password)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$fullName, $email, $hashedPassword]);

            return array(
                'success' => true,
                'message' => 'Registration successful'
            );
        } catch (PDOException $e) {
            return handleDBError($e);
        }
    }

    public function login($email, $password) {
        try {
            $stmt = $this->conn->prepare("
                SELECT user_id, full_name, email, password
                FROM users
                WHERE email = ? AND is_active = TRUE
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Update last login
                $stmt = $this->conn->prepare("
                    UPDATE users
                    SET last_login = CURRENT_TIMESTAMP
                    WHERE user_id = ?
                ");
                $stmt->execute([$user['user_id']]);

                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];

                return array(
                    'success' => true,
                    'message' => 'Login successful'
                );
            }

            return array(
                'success' => false,
                'message' => 'Invalid email or password'
            );
        } catch (PDOException $e) {
            return handleDBError($e);
        }
    }

    public function logout() {
        session_destroy();
        return array(
            'success' => true,
            'message' => 'Logged out successfully'
        );
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return array(
                'user_id' => $_SESSION['user_id'],
                'full_name' => $_SESSION['full_name'],
                'email' => $_SESSION['email']
            );
        }
        return null;
    }
}
?> 
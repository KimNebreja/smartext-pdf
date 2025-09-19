<?php
session_start();
require_once 'config/database.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate input
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $db = getDBConnection();
            if (!$db) {
                error_log("Login Error: Database connection failed in index.php (getDBConnection returned null)");
                throw new PDOException("Database connection failed");
            }
            error_log("Login process: Database connection successful.");

            error_log("Login process: Attempting to fetch user for email: " . $email);
            $stmt = $db->prepare("SELECT user_id, full_name, email, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Login process: User fetch query executed. Found user: " . (isset($user['user_id']) ? $user['user_id'] : 'None'));

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                header('Location: pages/dashboard.php');
            } else {
                $error = 'Invalid email or password';
                error_log("Login Error: Invalid email or password for email: " . $email);
            }
        } catch (PDOException $e) {
            error_log("Login Error: General database exception caught during login for email " . $email . ": " . $e->getMessage());
            $error = 'Login failed. Please try again later.';
        } catch (Exception $e) {
            error_log("Login Error: Unexpected exception caught during login for email " . $email . ": " . $e->getMessage());
            $error = 'An unexpected error occurred during login. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmarTextPDF - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="login-header">
        <img src="assets/SmarText PDF_sidebar-logo.svg" alt="SmarTextPDF Logo" class="main-logo">
    </div>
    <div class="login-container">
        <div class="login-box">
            <p class="subtitle">Login to your account</p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required
                        placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input-container">
                        <input type="password" id="password" name="password" required
                            placeholder="Enter your password">
                        <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>
            </form>

            <div class="login-link">
                Don't have an account? <a href="pages/signup.php">Create Account</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.toggle-password i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.classList.remove('fa-eye');
                toggleButton.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleButton.classList.remove('fa-eye-slash');
                toggleButton.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>
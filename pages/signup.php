<?php
session_start();
require_once '../config/database.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate input
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        try {
            $db = getDBConnection();
            
            // Check if email already exists
            $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered';
            } else {
                // Hash password and create user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (full_name, email, password, role, created_at) VALUES (?, ?, ?, 'user', NOW())");
                $stmt->execute([$full_name, $email, $hashed_password]);
                
                $success = 'Account created successfully! Please login.';
            }
        } catch (PDOException $e) {
            error_log("Signup Error: " . $e->getMessage());
            $error = 'Registration failed. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmarTextPDF - Create Account</title>
    <link rel="stylesheet" href="../styles/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .password-strength {
            margin-top: 5px;
            height: 5px;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        
        .strength-meter {
            display: flex;
            gap: 5px;
            margin-top: 5px;
        }
        
        .strength-meter div {
            flex: 1;
            height: 5px;
            border-radius: 3px;
            background-color: #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .strength-meter div.active {
            background-color: #ff4444;
        }
        
        .strength-meter div.active.moderate {
            background-color: #ffbb33;
        }
        
        .strength-meter div.active.strong {
            background-color: #00C851;
        }
        
        .password-requirements {
            margin-top: 10px;
            font-size: 0.9em;
            color: #666;
        }
        
        .password-requirements ul {
            list-style: none;
            padding-left: 0;
            margin: 5px 0;
        }
        
        .password-requirements li {
            margin: 3px 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .password-requirements li i {
            font-size: 0.8em;
        }
        
        .requirement-met {
            color: #00C851;
        }
        
        .requirement-unmet {
            color: #ff4444;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Create Account</h1>
            <div class="subtitle">
                <span>Join</span>
                <img src="../assets/SmarText PDF_main-logo.svg" alt="SmarTextPDF Logo" class="subtitle-logo">
                <span>today</span>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                    <br>
                    <a href="../index.php" class="btn-link">Go to Login</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form" id="signupForm">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required 
                           placeholder="Enter your full name">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input-container">
                        <input type="password" id="password" name="password" required 
                               placeholder="Create a password"
                               onkeyup="checkPasswordStrength(this.value)">
                        <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    
                    <div class="strength-meter">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                    
                    <div class="password-requirements">
                        <p>Password must contain:</p>
                        <ul>
                            <li id="length"><i class="fas fa-times"></i> At least 8 characters</li>
                            <li id="uppercase"><i class="fas fa-times"></i> One uppercase letter</li>
                            <li id="lowercase"><i class="fas fa-times"></i> One lowercase letter</li>
                            <li id="number"><i class="fas fa-times"></i> One number</li>
                            <li id="special"><i class="fas fa-times"></i> One special character</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-input-container">
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Confirm your password"
                               onkeyup="checkPasswordMatch()">
                        <button type="button" class="toggle-password" onclick="toggleConfirmPassword()" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="password-match-message" class="password-match-message"></div>
                </div>

                <button type="submit" class="btn-primary" id="signupBtn" disabled>
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>
            </form>

            <div class="login-link">
                Already have an account? <a href="../index.php">Login</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButtonIcon = document.querySelector('#password').parentElement.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButtonIcon.classList.remove('fa-eye');
                toggleButtonIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleButtonIcon.classList.remove('fa-eye-slash');
                toggleButtonIcon.classList.add('fa-eye');
            }
        }

        function toggleConfirmPassword() {
            const passwordInput = document.getElementById('confirm_password');
            const toggleButtonIcon = document.querySelector('#confirm_password').parentElement.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButtonIcon.classList.remove('fa-eye');
                toggleButtonIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleButtonIcon.classList.remove('fa-eye-slash');
                toggleButtonIcon.classList.add('fa-eye');
            }
        }

        function checkPasswordStrength(password) {
            const strengthMeter = document.querySelector('.strength-meter');
            const bars = strengthMeter.querySelectorAll('div');
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[^A-Za-z0-9]/.test(password)
            };

            // Update requirement indicators
            Object.keys(requirements).forEach(req => {
                const element = document.getElementById(req);
                const icon = element.querySelector('i');
                if (requirements[req]) {
                    element.classList.add('requirement-met');
                    element.classList.remove('requirement-unmet');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-check');
                } else {
                    element.classList.add('requirement-unmet');
                    element.classList.remove('requirement-met');
                    icon.classList.remove('fa-check');
                    icon.classList.add('fa-times');
                }
            });

            // Calculate strength
            const strength = Object.values(requirements).filter(Boolean).length;
            
            // Reset all bars
            bars.forEach(bar => {
                bar.classList.remove('active', 'moderate', 'strong');
            });

            // Update strength meter
            if (strength > 0) {
                bars[0].classList.add('active');
                if (strength > 2) {
                    bars[1].classList.add('active');
                    if (strength > 3) {
                        bars[2].classList.add('active');
                        if (strength > 4) {
                            bars[3].classList.add('active');
                        }
                    }
                }
            }

            // Add color classes based on strength
            if (strength <= 2) {
                bars.forEach(bar => bar.classList.add('weak'));
            } else if (strength === 3) {
                bars.forEach(bar => bar.classList.add('moderate'));
            } else {
                bars.forEach(bar => bar.classList.add('strong'));
            }

            checkPasswordMatch();
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const message = document.getElementById('password-match-message');
            const signupBtn = document.getElementById('signupBtn');

            if (confirmPassword === '') {
                message.textContent = '';
                signupBtn.disabled = true;
                return;
            }

            if (password === confirmPassword) {
                message.textContent = 'Passwords match';
                message.style.color = '#00C851';
                signupBtn.disabled = false;
            } else {
                message.textContent = 'Passwords do not match';
                message.style.color = '#ff4444';
                signupBtn.disabled = true;
            }
        }
    </script>
</body>
</html> 
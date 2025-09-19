-- Create the database
CREATE DATABASE IF NOT EXISTS proofread_pdf;
USE proofread_pdf;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    role VARCHAR(50) DEFAULT 'user',
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Uploads table
CREATE TABLE IF NOT EXISTS uploads (
    upload_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    custom_name VARCHAR(255),
    file_size INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_date TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_upload_date (upload_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Processing options table
CREATE TABLE IF NOT EXISTS processing_options (
    option_id INT PRIMARY KEY AUTO_INCREMENT,
    upload_id INT NOT NULL,
    check_grammar BOOLEAN DEFAULT TRUE,
    check_spelling BOOLEAN DEFAULT TRUE,
    check_style BOOLEAN DEFAULT FALSE,
    check_plagiarism BOOLEAN DEFAULT FALSE,
    language VARCHAR(10) DEFAULT 'en',
    FOREIGN KEY (upload_id) REFERENCES uploads(upload_id) ON DELETE CASCADE,
    INDEX idx_upload (upload_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Processed files table
CREATE TABLE IF NOT EXISTS processed_files (
    processed_id INT PRIMARY KEY AUTO_INCREMENT,
    upload_id INT NOT NULL,
    processed_file_path VARCHAR(255) NOT NULL,
    proof_data_path VARCHAR(255) NOT NULL,
    error_count INT DEFAULT 0,
    processing_time INT DEFAULT 0,
    processed_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (upload_id) REFERENCES uploads(upload_id) ON DELETE CASCADE,
    INDEX idx_upload (upload_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Error details table  
CREATE TABLE IF NOT EXISTS error_details (
    error_id INT PRIMARY KEY AUTO_INCREMENT,
    upload_id INT NOT NULL,
    error_type ENUM('grammar', 'spelling', 'style', 'plagiarism') NOT NULL,
    error_text TEXT NOT NULL,
    suggestion_text TEXT,
    page_number INT,
    line_number INT,
    is_accepted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (upload_id) REFERENCES uploads(upload_id) ON DELETE CASCADE,
    INDEX idx_upload (upload_id),
    INDEX idx_error_type (error_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User settings table
CREATE TABLE IF NOT EXISTS user_settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    default_language VARCHAR(10) DEFAULT 'en',
    default_grammar_check BOOLEAN DEFAULT TRUE,
    default_spelling_check BOOLEAN DEFAULT TRUE,
    default_style_check BOOLEAN DEFAULT FALSE,
    default_plagiarism_check BOOLEAN DEFAULT FALSE,
    theme VARCHAR(20) DEFAULT 'light',
    notifications_enabled BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123) only if it doesn't exist
INSERT IGNORE INTO users (full_name, email, password) VALUES 
('Admin User', 'admin@proofreadpdf.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert default settings for admin user only if it doesn't exist
INSERT IGNORE INTO user_settings (user_id) 
SELECT user_id FROM users WHERE email = 'admin@proofreadpdf.com' LIMIT 1;

-- Create triggers for automatic updates
DELIMITER //


-- Update last_login when user logs in
CREATE TRIGGER update_last_login
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.last_login IS NOT NULL AND (OLD.last_login IS NULL OR NEW.last_login != OLD.last_login) THEN
        UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = NEW.user_id;
    END IF;
END//

DELIMITER ; 
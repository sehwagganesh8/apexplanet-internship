<?php
// Database configuration for SQLite
define('DB_PATH', __DIR__ . '/database.sqlite');
define('UPLOAD_PATH', __DIR__ . '/courses/');
define('SESSION_NAME', 'apexplanet_elearning');

// Start session
session_start();

// Create uploads directory if it doesn't exist
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Initialize database using SQLite
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create tables
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(120) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    role ENUM('user', 'admin') DEFAULT 'user',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS courses (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title VARCHAR(200) NOT NULL,
                    instructor VARCHAR(100) NOT NULL,
                    price DECIMAL(10, 2) NOT NULL,
                    duration DECIMAL(5, 2) NOT NULL DEFAULT 10.0,
                    description TEXT,
                    cover_image VARCHAR(255) DEFAULT 'default-cover.jpg',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS enrollments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER,
                    course_id INTEGER,
                    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    completed tinyint(1) DEFAULT 0,
                    FOREIGN KEY (user_id) REFERENCES users(id),
                    FOREIGN KEY (course_id) REFERENCES courses(id)
                )
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS otp_codes (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER,
                    code VARCHAR(6) NOT NULL,
                    expires_at TIMESTAMP NOT NULL,
                    used tinyint(1) DEFAULT 0,
                    FOREIGN KEY (user_id) REFERENCES users(id)
                )
            ");

            // Create default admin if no users exist
            $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            if ($count == 0) {
                $hashedPwd = password_hash('admin123', PASSWORD_DEFAULT);
                $pdo->prepare("
                    INSERT INTO users (name, email, password, role)
                    VALUES (:name, :email, :password, :role)
                ")->execute([
                    'name' => 'Admin',
                    'email' => 'admin@elearn.com',
                    'password' => $hashedPwd,
                    'role' => 'admin'
                ]);
            }

            // Create default cover image placeholder if not exists
            $coverPath = UPLOAD_PATH . 'default-cover.jpg';
            if (!file_exists($coverPath)) {
                file_put_contents($coverPath, '');
            }

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            die("Database connection failed.");
        }
    }
    return $pdo;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) return null;

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Login required middleware
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php?action=login');
        exit;
    }
}

// Admin required middleware
function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
}

// Logout
function doLogout() {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// Form helper
function sanitizeInput($data) {
    return trim(stripslashes(htmlspecialchars($data)));
}

// Password hashing
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Password verification
function verifyPassword($password, $hashed) {
    return password_verify($password, $hashed);
}

// File upload helper
function uploadFile($file, $path = null) {
    if ($path === null) $path = UPLOAD_PATH;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error'];
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Only JPG, PNG, GIF, WebP allowed'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Max file size: 2MB'];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('course_') . '.' . $ext;
    $destination = $path . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename];
    }

    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

// OTP helpers
function generateOTP() {
    return sprintf('%06d', rand(0, 999999));
}

function sendOTP($userId, $pdo) {
    // Generate and store OTP
    $code = generateOTP();
    $expires = date('Y-m-d H:i:s', time() + 300); // 5 minutes

    // Remove existing OTP for this user
    $pdo->prepare("DELETE FROM otp_codes WHERE user_id = :user_id")->execute([':user_id' => $userId]);

    // Store new OTP
    $stmt = $pdo->prepare("
        INSERT INTO otp_codes (user_id, code, expires_at)
        VALUES (:user_id, :code, :expires_at)
    ");
    $stmt->execute([':user_id' => $userId, ':code' => $code, ':expires_at' => $expires]);

    // In a real app, would send via email here
    // mail($userEmail, 'Your OTP Code', "Your verification code is: $code");

    return ['success' => true, 'code' => $code];
}

function verifyOTP($code, $pdo) {
    $stmt = $pdo->prepare("
        SELECT * FROM otp_codes WHERE code = :code AND used = 0 AND expires_at > NOW()
    ");
    $stmt->execute([':code' => $code]);
    $otp = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($otp) {
        // Mark as used
        $pdo->prepare("UPDATE otp_codes SET used = 1 WHERE id = :id")->execute([':id' => $otp['id']]);
        return ['success' => true];
    }

    return ['success' => false, 'message' => 'Invalid or expired OTP'];
}
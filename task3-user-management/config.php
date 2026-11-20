<?php
// Database configuration for SQLite
define('DB_PATH', __DIR__ . '/database.sqlite');
define('SESSION_NAME', 'apexplanet_user');

// Start session
session_start();

// Initialize database
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create tables on first connection
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(120) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    role ENUM('user', 'admin') DEFAULT 'user',
                    profile_pic VARCHAR(255) DEFAULT 'default.png',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
                    'email' => 'admin@apexplane.com',
                    'password' => $hashedPwd,
                    'role' => 'admin'
                ]);
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
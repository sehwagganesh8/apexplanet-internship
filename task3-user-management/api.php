<?php
require __DIR__ . '/config.php';

// Handle API actions
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

try {
    switch ($action) {
        case 'login':
            login();
            break;
        case 'register':
            register();
            break;
        case 'logout':
            doLogout();
            break;
        case 'add-user':
            addUser();
            break;
        case 'edit-user':
            editUser();
            break;
        case 'list-users':
            listUsers();
            break;
        case 'delete-user':
            deleteUser();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function login() {
    $pdo = getDB();
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password required']);
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && verifyPassword($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        echo json_encode(['success' => true, 'user' => ['id' => $user['id'], 'name' => $user['name'], 'role' => $user['role']]]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }
}

function register() {
    $pdo = getDB();
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields required']);
        return;
    }

    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
        return;
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        return;
    }

    $hashedPwd = hashPassword($password);

    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role)
        VALUES (:name, :email, :password, 'user')
    ");
    if ($stmt->execute([':name' => $name, ':email' => $email, ':password' => $hashedPwd])) {
        echo json_encode(['success' => true, 'message' => 'Account created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed']);
    }
}

function addUser() {
    global $pdo;
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields required']);
        return;
    }

    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
        return;
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        return;
    }

    $hashedPwd = hashPassword($password);

    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role)
        VALUES (:name, :email, :password, :role)
    ");
    if ($stmt->execute([':name' => $name, ':email' => $email, ':password' => $hashedPwd, ':role' => $role])) {
        echo json_encode(['success' => true, 'message' => 'User added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add user']);
    }
}

function editUser() {
    global $pdo;
    $userId = (int)($_POST['user_id'] ?? 0);
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'user';

    if (empty($name) || empty($email) || $userId === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        return;
    }

    // Check if email already exists for another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
    $stmt->execute([':email' => $email, ':id' => $userId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered by another user']);
        return;
    }

    $stmt = $pdo->prepare("
        UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id
    ");
    if ($stmt->execute([':name' => $name, ':email' => $email, ':role' => $role, ':id' => $userId])) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update user']);
    }
}

function listUsers() {
    global $pdo;
    $stmt = $pdo->query("SELECT id, name, email, role, profile_pic, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dates
    foreach ($users as &$user) {
        $date = strtotime($user['created_at']);
        $user['created_at'] = date('M d, Y', $date);
    }
    unset($user);

    echo json_encode(['success' => true, 'users' => $users]);
}

function deleteUser() {
    global $pdo;
    $userId = (int)($_POST['user_id'] ?? 0);

    if ($userId === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }

    // Don't allow deleting the last admin
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    if ($stmt->execute([':id' => $userId])) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
    }
}
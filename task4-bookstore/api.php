<?php
require __DIR__ . '/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$bookId = (int)($_GET['id'] ?? 0);

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
        case 'get-stats':
            getStats();
            break;
        case 'list-books':
            listBooks();
            break;
        case 'get-book':
            getBook();
            break;
        case 'add-book':
            addBook();
            break;
        case 'edit-book':
            editBook();
            break;
        case 'delete-book':
            deleteBook();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
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

function getStats() {
    $pdo = getDB();

    $totalBooks = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
    $totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    echo json_encode(['success' => true, 'stats' => [
        'totalBooks' => (int)$totalBooks,
        'totalOrders' => (int)$totalOrders,
        'totalUsers' => (int)$totalUsers
    ]]);
}

function listBooks() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT id, title, author, price, category, stock, cover_image FROM books ORDER BY created_at DESC");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'books' => $books]);
}

function getBook() {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = :id");
    $stmt->execute([':id' => $bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($book) {
        echo json_encode(['success' => true, 'book' => $book]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Book not found']);
    }
}

function addBook() {
    global $pdo;

    // Handle cover image upload
    $coverResult = ['filename' => 'default-cover.jpg'];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $coverResult = uploadFile($_FILES['cover_image']);
        if (!$coverResult['success']) {
            echo json_encode(['success' => false, 'message' => $coverResult['message']]);
            return;
        }
    }

    $title = sanitizeInput($_POST['title'] ?? '');
    $author = sanitizeInput($_POST['author'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $category = sanitizeInput($_POST['category'] ?? 'fiction');
    $stock = (int)($_POST['stock'] ?? 0);

    if (empty($title) || empty($author)) {
        echo json_encode(['success' => false, 'message' => 'Title and author required']);
        return;
    }

    $coverImage = $coverResult['filename'];

    $stmt = $pdo->prepare("
        INSERT INTO books (title, author, price, category, stock, cover_image)
        VALUES (:title, :author, :price, :category, :stock, :cover_image)
    ");
    if ($stmt->execute([
        ':title' => $title,
        ':author' => $author,
        ':price' => $price,
        ':category' => $category,
        ':stock' => $stock,
        ':cover_image' => $coverImage
    ])) {
        echo json_encode(['success' => true, 'message' => 'Book added successfully', 'cover' => $coverImage]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add book']);
    }
}

function editBook() {
    global $pdo;

    // Handle cover image upload if new image provided
    $coverImage = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($_FILES['cover_image']);
        if ($uploadResult['success']) {
            $coverImage = $uploadResult['filename'];
        }
    } else {
        // Keep existing cover
        $coverImage = $_POST['existingCover'] ?? 'default-cover.jpg';
    }

    $title = sanitizeInput($_POST['title'] ?? '');
    $author = sanitizeInput($_POST['author'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $category = sanitizeInput($_POST['category'] ?? 'fiction');
    $stock = (int)($_POST['stock'] ?? 0);

    if (empty($title) || empty($author) || $bookId === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        return;
    }

    $stmt = $pdo->prepare("
        UPDATE books SET title = :title, author = :author, price = :price,
                         category = :category, stock = :stock, cover_image = :cover_image
        WHERE id = :id
    ");
    if ($stmt->execute([
        ':title' => $title,
        ':author' => $author,
        ':price' => $price,
        ':category' => $category,
        ':stock' => $stock,
        ':cover_image' => $coverImage,
        ':id' => $bookId
    ])) {
        echo json_encode(['success' => true, 'message' => 'Book updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update book']);
    }
}

function deleteBook() {
    global $pdo;

    // Handle cover image deletion
    $bookStmt = $pdo->prepare("SELECT cover_image FROM books WHERE id = :id");
    $bookStmt->execute([':id' => $bookId]);
    $book = $bookStmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        echo json_encode(['success' => false, 'message' => 'Book not found']);
        return;
    }

    // Delete cover image file if it's not the default
    if ($book['cover_image'] !== 'default-cover.jpg') {
        $coverPath = __DIR__ . '/uploads/' . $book['cover_image'];
        if (file_exists($coverPath)) {
            unlink($coverPath);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM books WHERE id = :id");
    if ($stmt->execute([':id' => $bookId])) {
        echo json_encode(['success' => true, 'message' => 'Book deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete book']);
    }
}
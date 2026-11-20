<?php
require __DIR__ . '/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$userId = (int)($_GET['user_id'] ?? 0);
$courseId = (int)($_GET['course_id'] ?? 0);

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
        case 'list-courses':
            listCourses();
            break;
        case 'get-course':
            getCourse();
            break;
        case 'add-course':
            addCourse();
            break;
        case 'edit-course':
            editCourse();
            break;
        case 'delete-course':
            deleteCourse();
            break;
        case 'enroll-course':
            enrollCourse();
            break;
        case 'verify-otp':
            verifyOTPAction();
            break;
        case 'resend-otp':
            resendOTPAction();
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
        // Generate OTP for new user
        sendOTP($userId, $pdo);
        echo json_encode(['success' => true, 'message' => 'Account created successfully. Please verify your email.', 'needsOtp' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed']);
    }
}

function getStats() {
    $pdo = getDB();

    $totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
    $totalStudents = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();

    echo json_encode(['success' => true, 'stats' => [
        'totalCourses' => (int)$totalCourses,
        'totalStudents' => (int)$totalStudents
    ]]);
}

function listCourses() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT c.id, c.title, c.instructor, c.price, c.duration, c.cover_image,
                        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as students
                        FROM courses c ORDER BY c.created_at DESC");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'courses' => $courses]);
}

function getCourse() {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = :id");
    $stmt->execute([':id' => $courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($course) {
        echo json_encode(['success' => true, 'course' => $course]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Course not found']);
    }
}

function addCourse() {
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
    $instructor = sanitizeInput($_POST['instructor'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $duration = (float)($_POST['duration'] ?? 10.0);
    $description = sanitizeInput($_POST['description'] ?? '');

    if (empty($title) || empty($instructor)) {
        echo json_encode(['success' => false, 'message' => 'Title and instructor required']);
        return;
    }

    $coverImage = $coverResult['filename'];

    $stmt = $pdo->prepare("
        INSERT INTO courses (title, instructor, price, duration, description, cover_image)
        VALUES (:title, :instructor, :price, :duration, :description, :cover_image)
    ");
    if ($stmt->execute([
        ':title' => $title,
        ':instructor' => $instructor,
        ':price' => $price,
        ':duration' => $duration,
        ':description' => $description,
        ':cover_image' => $coverImage
    ])) {
        echo json_encode(['success' => true, 'message' => 'Course added successfully', 'cover' => $coverImage]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add course']);
    }
}

function editCourse() {
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
    $instructor = sanitizeInput($_POST['instructor'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $duration = (float)($_POST['duration'] ?? 10.0);
    $description = sanitizeInput($_POST['description'] ?? '');

    if (empty($title) || empty($instructor) || $courseId === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        return;
    }

    $stmt = $pdo->prepare("
        UPDATE courses SET title = :title, instructor = :instructor, price = :price,
                         duration = :duration, description = :description, cover_image = :cover_image
        WHERE id = :id
    ");
    if ($stmt->execute([
        ':title' => $title,
        ':instructor' => $instructor,
        ':price' => $price,
        ':duration' => $duration,
        ':description' => $description,
        ':cover_image' => $coverImage,
        ':id' => $courseId
    ])) {
        echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update course']);
    }
}

function deleteCourse() {
    global $pdo;

    $stmt = $pdo->prepare("SELECT cover_image FROM courses WHERE id = :id");
    $stmt->execute([':id' => $courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        echo json_encode(['success' => false, 'message' => 'Course not found']);
        return;
    }

    // Delete cover image file if not default
    if ($course['cover_image'] !== 'default-cover.jpg') {
        $coverPath = __DIR__ . '/courses/' . $course['cover_image'];
        if (file_exists($coverPath)) {
            unlink($coverPath);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = :id");
    if ($stmt->execute([':id' => $courseId])) {
        // Also delete related enrollments
        $pdo->prepare("DELETE FROM enrollments WHERE course_id = :id")->execute([':id' => $courseId]);
        echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete course']);
    }
}

function enrollCourse() {
    global $pdo;

    $userId = (int)($_POST['user_id'] ?? 0);
    $courseId = (int)($_POST['course_id'] ?? 0);

    if ($userId === 0 || $courseId === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user or course ID']);
        return;
    }

    // Check if already enrolled
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = :user_id AND course_id = :course_id");
    $stmt->execute([':user_id' => $userId, ':course_id' => $courseId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Already enrolled in this course']);
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO enrollments (user_id, course_id)
        VALUES (:user_id, :course_id)
    ");
    if ($stmt->execute([':user_id' => $userId, ':course_id' => $courseId])) {
        echo json_encode(['success' => true, 'message' => 'Enrolled successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to enroll']);
    }
}

function verifyOTPAction() {
    $pdo = getDB();
    $code = $_POST['code'] ?? '';

    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => 'OTP code required']);
        return;
    }

    $result = verifyOTP($code, $pdo);
    if ($result['success']) {
        // Mark user as verified in session
        $_SESSION['user_verified'] = true;
        echo json_encode(['success' => true, 'message' => 'Email verified successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => $result['message']]);
    }
}

function resendOTPAction() {
    global $pdo;
    $userId = (int)($_POST['user_id'] ?? 0);

    if ($userId === 0) {
        echo json_encode(['success' => false, 'message' => 'User ID required']);
        return;
    }

    $result = sendOTP($userId, $pdo);
    if ($result['success']) {
        // Reset OTP timer in UI
        echo json_encode(['success' => true, 'message' => 'New OTP sent']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP']);
    }
}
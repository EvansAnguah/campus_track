<?php
/**
 * Campus Track - Authentication API Endpoints
 * 
 * Handles: register, login, logout, session verification
 */

require_once 'config.php';
require_once 'Auth.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$auth = new Auth();

try {
    switch ($action) {
        case 'register':
            handleRegister();
            break;

        case 'login':
            handleLogin();
            break;

        case 'logout':
            handleLogout();
            break;

        case 'verify':
            handleVerify();
            break;

        case 'current-user':
            handleCurrentUser();
            break;

        case 'create-lecturer':
            handleCreateLecturer();
            break;

        default:
            sendError('Invalid action', 400);
    }
} catch (Exception $e) {
    sendError('Server error: ' . $e->getMessage(), 500);
}

/**
 * Register new student
 */
function handleRegister() {
    global $auth;

    $data = json_decode(file_get_contents('php://input'), true);

    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $passwordConfirm = $data['passwordConfirm'] ?? '';
    $indexNumber = trim($data['indexNumber'] ?? '');
    $fullName = trim($data['fullName'] ?? '');

    // Validate
    if (!$email || !$password || !$indexNumber || !$fullName) {
        sendError('All fields are required');
    }

    if ($password !== $passwordConfirm) {
        sendError('Passwords do not match');
    }

    // Register
    $result = $auth->registerStudent($email, $password, $indexNumber, $fullName);
    
    if ($result['success']) {
        sendSuccess($result['message']);
    } else {
        sendError($result['message']);
    }
}

/**
 * Login user
 */
function handleLogin() {
    global $auth;

    $data = json_decode(file_get_contents('php://input'), true);

    $identifier = trim($data['identifier'] ?? '');
    $password = $data['password'] ?? '';
    $userType = trim($data['userType'] ?? '');

    // Validate
    if (!$identifier || !$password || !$userType) {
        sendError('All fields are required');
    }

    if (!in_array($userType, ['student', 'lecturer'])) {
        sendError('Invalid user type');
    }

    // Login
    $result = $auth->login($identifier, $password, $userType);
    
    if ($result['success']) {
        sendSuccess($result['message'], ['user' => $result['user']]);
    } else {
        sendError($result['message'], 401);
    }
}

/**
 * Logout user
 */
function handleLogout() {
    global $auth;

    $data = json_decode(file_get_contents('php://input'), true);
    $token = $data['token'] ?? '';

    if (!$token) {
        sendError('Token required');
    }

    $result = $auth->logout($token);

    if ($result['success']) {
        sendSuccess($result['message']);
    } else {
        sendError($result['message']);
    }
}

/**
 * Verify token
 */
function handleVerify() {
    global $auth;

    $data = json_decode(file_get_contents('php://input'), true);
    $token = $data['token'] ?? '';

    if (!$token) {
        sendError('Token required', 401);
    }

    $session = $auth->verifyToken($token);

    if (!$session) {
        sendError('Invalid or expired token', 401);
    }

    sendSuccess('Token valid', ['session' => $session]);
}

/**
 * Get current logged-in user
 */
function handleCurrentUser() {
    global $auth;

    $data = json_decode(file_get_contents('php://input'), true);
    $token = $data['token'] ?? '';

    if (!$token) {
        sendError('Token required', 401);
    }

    $user = $auth->getCurrentUser($token);

    if (!$user) {
        sendError('User not found', 404);
    }

    sendSuccess('User data retrieved', ['user' => $user]);
}

/**
 * Create new lecturer account (admin function)
 */
function handleCreateLecturer() {
    global $auth;

    $data = json_decode(file_get_contents('php://input'), true);

    $fullName = trim($data['fullName'] ?? '');
    $email = trim($data['email'] ?? '');
    $employeeId = trim($data['employeeId'] ?? '');
    $department = trim($data['department'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $password = $data['password'] ?? '';
    $passwordConfirm = $data['passwordConfirm'] ?? '';

    // Validate
    if (!$fullName || !$email || !$employeeId || !$department || !$password || !$passwordConfirm) {
        sendError('All fields are required');
    }

    if ($password !== $passwordConfirm) {
        sendError('Passwords do not match');
    }

    // Create lecturer
    $result = $auth->createLecturer($fullName, $email, $employeeId, $department, $phone, $password);
    
    if ($result['success']) {
        sendSuccess($result['message']);
    } else {
        sendError($result['message']);
    }
}

?>

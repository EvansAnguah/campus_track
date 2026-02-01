<?php
/**
 * Campus Track - Lecturer Management API
 * 
 * Handles course and student management endpoints
 */

require_once 'config.php';
require_once 'Auth.php';
require_once 'LecturerManager.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$auth = new Auth();
$manager = new LecturerManager();

try {
    switch ($action) {
        case 'get-courses':
            handleGetCourses();
            break;

        case 'create-course':
            handleCreateCourse();
            break;

        case 'get-students':
            handleGetStudents();
            break;

        case 'add-student':
            handleAddStudent();
            break;

        case 'student-summary':
            handleStudentSummary();
            break;

        default:
            sendError('Invalid action', 400);
    }
} catch (Exception $e) {
    sendError('Server error: ' . $e->getMessage(), 500);
}

/**
 * Get all courses for a lecturer
 */
function handleGetCourses() {
    global $auth, $manager;

    $data = json_decode(file_get_contents('php://input'), true);
    $token = $data['token'] ?? '';

    if (!$token) {
        sendError('Token required', 401);
    }

    $session = $auth->verifyToken($token);
    if (!$session) {
        sendError('Invalid token', 401);
    }

    if ($session['user_type'] !== 'lecturer') {
        sendError('Only lecturers can access courses', 403);
    }

    $courses = $manager->getCourses($session['profile_id']);
    sendSuccess('Courses retrieved', ['courses' => $courses]);
}

/**
 * Create new course
 */
function handleCreateCourse() {
    global $auth, $manager;

    $data = json_decode(file_get_contents('php://input'), true);

    $token = $data['token'] ?? '';
    $code = trim($data['code'] ?? '');
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');

    if (!$token) {
        sendError('Token required', 401);
    }

    $session = $auth->verifyToken($token);
    if (!$session) {
        sendError('Invalid token', 401);
    }

    if ($session['user_type'] !== 'lecturer') {
        sendError('Only lecturers can create courses', 403);
    }

    if (!$code || !$name) {
        sendError('Course code and name are required');
    }

    $result = $manager->createCourse($session['profile_id'], $code, $name, $description);

    if ($result['success']) {
        sendSuccess($result['message'], ['courseId' => $result['courseId']]);
    } else {
        sendError($result['message']);
    }
}

/**
 * Get all students
 */
function handleGetStudents() {
    global $auth, $manager;

    $data = json_decode(file_get_contents('php://input'), true);
    $token = $data['token'] ?? '';

    if (!$token) {
        sendError('Token required', 401);
    }

    $session = $auth->verifyToken($token);
    if (!$session) {
        sendError('Invalid token', 401);
    }

    if ($session['user_type'] !== 'lecturer') {
        sendError('Only lecturers can view students', 403);
    }

    $students = $manager->getAllStudents();
    sendSuccess('Students retrieved', ['students' => $students]);
}

/**
 * Add student manually
 */
function handleAddStudent() {
    global $auth, $manager;

    $data = json_decode(file_get_contents('php://input'), true);

    $token = $data['token'] ?? '';
    $email = trim($data['email'] ?? '');
    $indexNumber = trim($data['indexNumber'] ?? '');
    $fullName = trim($data['fullName'] ?? '');
    $temporaryPassword = $data['temporaryPassword'] ?? uniqid('temp_');

    if (!$token) {
        sendError('Token required', 401);
    }

    $session = $auth->verifyToken($token);
    if (!$session) {
        sendError('Invalid token', 401);
    }

    if ($session['user_type'] !== 'lecturer') {
        sendError('Only lecturers can add students', 403);
    }

    if (!$email || !$indexNumber || !$fullName) {
        sendError('Email, index number, and name are required');
    }

    $result = $manager->addStudent($email, $indexNumber, $fullName, $temporaryPassword);

    if ($result['success']) {
        sendSuccess(
            $result['message'],
            ['temporaryPassword' => $result['temporaryPassword']]
        );
    } else {
        sendError($result['message']);
    }
}

/**
 * Get student attendance summary
 */
function handleStudentSummary() {
    global $auth, $manager;

    $data = json_decode(file_get_contents('php://input'), true);

    $token = $data['token'] ?? '';
    $studentId = $data['studentId'] ?? '';

    if (!$token) {
        sendError('Token required', 401);
    }

    $session = $auth->verifyToken($token);
    if (!$session) {
        sendError('Invalid token', 401);
    }

    if ($session['user_type'] !== 'lecturer') {
        sendError('Only lecturers can view summaries', 403);
    }

    if (!$studentId) {
        sendError('Student ID required');
    }

    $summary = $manager->getStudentAttendanceSummary(intval($studentId));
    sendSuccess('Summary retrieved', ['summary' => $summary]);
}

?>

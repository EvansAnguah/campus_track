<?php
/**
 * Campus Track - Attendance API Endpoints
 * 
 * Handles attendance marking, session management, and history
 */

require_once 'config.php';
require_once 'Auth.php';
require_once 'Attendance.php';
require_once 'EmailNotifier.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$auth = new Auth();
$attendance = new Attendance();

try {
    switch ($action) {
        case 'get-active-sessions':
            handleGetActiveSessions();
            break;

        case 'mark-attendance':
            handleMarkAttendance();
            break;

        case 'student-history':
            handleStudentHistory();
            break;

        case 'create-session':
            handleCreateSession();
            break;

        case 'end-session':
            handleEndSession();
            break;

        case 'lecturer-sessions':
            handleLecturerSessions();
            break;

        case 'session-report':
            handleSessionReport();
            break;

        default:
            sendError('Invalid action', 400);
    }
} catch (Exception $e) {
    sendError('Server error: ' . $e->getMessage(), 500);
}

/**
 * Get all active attendance sessions
 */
function handleGetActiveSessions() {
    global $auth, $attendance;

    $data = json_decode(file_get_contents('php://input'), true);
    $token = $data['token'] ?? '';

    if (!$token) {
        sendError('Token required', 401);
    }

    if (!$auth->verifyToken($token)) {
        sendError('Invalid token', 401);
    }

    $sessions = $attendance->getActiveSessions();
    sendSuccess('Active sessions retrieved', ['sessions' => $sessions]);
}

/**
 * Mark student attendance
 * Validates location and session status server-side
 */
function handleMarkAttendance() {
    global $auth, $attendance;

    $data = json_decode(file_get_contents('php://input'), true);

    $token = $data['token'] ?? '';
    $sessionId = $data['sessionId'] ?? '';
    $latitude = $data['latitude'] ?? '';
    $longitude = $data['longitude'] ?? '';

    // Validate token
    if (!$token) {
        sendError('Token required', 401);
    }

    $session = $auth->verifyToken($token);
    if (!$session) {
        sendError('Invalid token', 401);
    }

    if ($session['user_type'] !== 'student') {
        sendError('Only students can mark attendance', 403);
    }

    // Validate inputs
    if (!$sessionId || !is_numeric($latitude) || !is_numeric($longitude)) {
        sendError('Session ID, latitude, and longitude are required');
    }

    $latitude = floatval($latitude);
    $longitude = floatval($longitude);
    $sessionId = intval($sessionId);
    $studentId = $session['profile_id'];

    // Mark attendance with server-side validation
    $result = $attendance->markAttendance($sessionId, $studentId, $latitude, $longitude);

    if ($result['success']) {
        // Send email notification
        $notifier = new EmailNotifier();
        
        // Get student info
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT s.full_name, u.email, c.code, c.name, s.id
            FROM students s
            JOIN users u ON s.user_id = u.id
            JOIN attendance_records ar ON s.id = ar.student_id
            JOIN attendance_sessions sess ON ar.session_id = sess.id
            JOIN courses c ON sess.course_id = c.id
            WHERE s.id = ? AND sess.id = ?
        ");
        $stmt->bind_param('ii', $studentId, $sessionId);
        $stmt->execute();
        $studentResult = $stmt->get_result();

        if ($studentResult->num_rows > 0) {
            $student = $studentResult->fetch_assoc();
            $markedTime = date('Y-m-d H:i:s');
            
            // Send confirmation email
            $notifier->sendAttendanceConfirmation(
                $student['email'],
                $student['full_name'],
                $student['code'],
                $student['name'],
                $markedTime,
                0, // distance will be calculated from record
                0  // radius - we'll fetch these from session
            );
        }
        $stmt->close();

        sendSuccess($result['message']);
    } else {
        sendError($result['message']);
    }
}

/**
 * Get student attendance history
 */
function handleStudentHistory() {
    global $auth, $attendance;

    $data = json_decode(file_get_contents('php://input'), true);

    $token = $data['token'] ?? '';
    $period = $data['period'] ?? 'all';

    if (!$token) {
        sendError('Token required', 401);
    }

    $session = $auth->verifyToken($token);
    if (!$session) {
        sendError('Invalid token', 401);
    }

    if ($session['user_type'] !== 'student') {
        sendError('Only students can view their history', 403);
    }

    if (!in_array($period, ['all', 'week', 'month'])) {
        $period = 'all';
    }

    $studentId = $session['profile_id'];
    $history = $attendance->getStudentAttendanceHistory($studentId, $period);

    sendSuccess('History retrieved', ['history' => $history]);
}

/**
 * Create new attendance session (lecturer only)
 */
function handleCreateSession() {
    global $auth, $attendance;

    $data = json_decode(file_get_contents('php://input'), true);

    $token = $data['token'] ?? '';
    $courseId = $data['courseId'] ?? '';
    $latitude = $data['latitude'] ?? '';
    $longitude = $data['longitude'] ?? '';
    $radiusMeters = $data['radiusMeters'] ?? '';

    if (!$token) {
        sendError('Token required', 401);
    }

    $session = $auth->verifyToken($token);
    if (!$session) {
        sendError('Invalid token', 401);
    }

    if ($session['user_type'] !== 'lecturer') {
        sendError('Only lecturers can create sessions', 403);
    }

    // Validate inputs
    if (!$courseId || !is_numeric($latitude) || !is_numeric($longitude) || !is_numeric($radiusMeters)) {
        sendError('All session parameters are required');
    }

    $result = $attendance->createSession(
        intval($courseId),
        floatval($latitude),
        floatval($longitude),
        intval($radiusMeters)
    );

    if ($result['success']) {
        sendSuccess($result['message'], ['sessionId' => $result['sessionId']]);
    } else {
        sendError($result['message']);
    }
}

/**
 * End attendance session
 */
function handleEndSession() {
    global $auth, $attendance;

    $data = json_decode(file_get_contents('php://input'), true);

    $token = $data['token'] ?? '';
    $sessionId = $data['sessionId'] ?? '';

    if (!$token) {
        sendError('Token required', 401);
    }

    $session = $auth->verifyToken($token);
    if (!$session) {
        sendError('Invalid token', 401);
    }

    if ($session['user_type'] !== 'lecturer') {
        sendError('Only lecturers can end sessions', 403);
    }

    if (!$sessionId) {
        sendError('Session ID required');
    }

    $result = $attendance->endSession(intval($sessionId));

    if ($result['success']) {
        sendSuccess($result['message']);
    } else {
        sendError($result['message']);
    }
}

/**
 * Get lecturer's sessions with attendance counts
 */
function handleLecturerSessions() {
    global $auth, $attendance;

    $data = json_decode(file_get_contents('php://input'), true);

    $token = $data['token'] ?? '';
    $period = $data['period'] ?? 'all';

    if (!$token) {
        sendError('Token required', 401);
    }

    $session = $auth->verifyToken($token);
    if (!$session) {
        sendError('Invalid token', 401);
    }

    if ($session['user_type'] !== 'lecturer') {
        sendError('Only lecturers can view sessions', 403);
    }

    if (!in_array($period, ['all', 'week', 'month'])) {
        $period = 'all';
    }

    $lecturerId = $session['profile_id'];
    $sessions = $attendance->getLecturerSessions($lecturerId, $period);

    sendSuccess('Sessions retrieved', ['sessions' => $sessions]);
}

/**
 * Get detailed attendance report for a session
 */
function handleSessionReport() {
    global $auth, $attendance;

    $data = json_decode(file_get_contents('php://input'), true);

    $token = $data['token'] ?? '';
    $sessionId = $data['sessionId'] ?? '';

    if (!$token) {
        sendError('Token required', 401);
    }

    $session = $auth->verifyToken($token);
    if (!$session) {
        sendError('Invalid token', 401);
    }

    if ($session['user_type'] !== 'lecturer') {
        sendError('Only lecturers can view reports', 403);
    }

    if (!$sessionId) {
        sendError('Session ID required');
    }

    $report = $attendance->getSessionAttendanceReport(intval($sessionId));
    sendSuccess('Report retrieved', ['report' => $report]);
}

?>

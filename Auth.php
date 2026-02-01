<?php
/**
 * Campus Track - Authentication Handler
 * 
 * Handles user registration, login, logout, and session management
 */

require_once 'config.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Register a new student
     * 
     * @param string $email
     * @param string $password
     * @param string $indexNumber
     * @param string $fullName
     * @return array
     */
    public function registerStudent($email, $password, $indexNumber, $fullName) {
        // Validate inputs
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        if (strlen($fullName) < 2) {
            return ['success' => false, 'message' => 'Name must be at least 2 characters'];
        }

        if (empty($indexNumber)) {
            return ['success' => false, 'message' => 'Index number is required'];
        }

        // Check if email already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Email already registered'];
        }
        $stmt->close();

        // Check if index number already exists
        $stmt = $this->db->prepare("SELECT id FROM students WHERE index_number = ?");
        $stmt->bind_param('s', $indexNumber);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Index number already registered'];
        }
        $stmt->close();

        // Hash password
        $passwordHash = hashPassword($password);

        // Create user
        $stmt = $this->db->prepare("INSERT INTO users (email, password_hash, user_type) VALUES (?, ?, ?)");
        $userType = 'student';
        $stmt->bind_param('sss', $email, $passwordHash, $userType);
        
        if (!$stmt->execute()) {
            $stmt->close();
            return ['success' => false, 'message' => 'Registration failed'];
        }
        
        $userId = $this->db->getConnection()->insert_id;
        $stmt->close();

        // Create student profile
        $stmt = $this->db->prepare("INSERT INTO students (user_id, index_number, full_name) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $userId, $indexNumber, $fullName);
        
        if (!$stmt->execute()) {
            // Rollback - delete user
            $this->db->prepare("DELETE FROM users WHERE id = ?")->bind_param('i', $userId)->execute();
            $stmt->close();
            return ['success' => false, 'message' => 'Student profile creation failed'];
        }
        
        $stmt->close();

        return ['success' => true, 'message' => 'Registration successful'];
    }

    /**
     * Login user (student or lecturer)
     * 
     * @param string $identifier Email for lecturer, index number for student
     * @param string $password
     * @param string $userType 'student' or 'lecturer'
     * @return array
     */
    public function login($identifier, $password, $userType) {
        $db = Database::getInstance()->getConnection();

        // Determine query based on user type
        if ($userType === 'student') {
            $query = "SELECT u.id, u.email, u.password_hash, s.index_number, s.full_name, s.id as student_id 
                      FROM users u 
                      JOIN students s ON u.id = s.user_id 
                      WHERE s.index_number = ? AND u.user_type = 'student'";
        } else {
            $query = "SELECT u.id, u.email, u.password_hash, l.id as lecturer_id, l.employee_id, l.department 
                      FROM users u 
                      JOIN lecturers l ON u.id = l.user_id 
                      WHERE u.email = ? AND u.user_type = 'lecturer'";
        }

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $identifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Verify password
        if (!verifyPassword($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // Check for active session lock (only for students)
        if ($userType === 'student') {
            $lockCheck = $this->checkDeviceLock($user['student_id']);
            if (!$lockCheck['canProceed']) {
                return ['success' => false, 'message' => $lockCheck['message']];
            }
        }

        // Generate session token
        $token = generateToken();
        $deviceId = generateDeviceId();
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_TIMEOUT);

        // Create user session
        $stmt = $this->db->prepare("INSERT INTO user_sessions (user_id, token, device_id, ip_address, expires_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('issss', $user['id'], $token, $deviceId, $ipAddress, $expiresAt);
        
        if (!$stmt->execute()) {
            $stmt->close();
            return ['success' => false, 'message' => 'Login failed'];
        }
        
        $stmt->close();

        // Return user data with token
        $userData = [
            'id' => $user['id'],
            'email' => $user['email'],
            'userType' => $userType,
            'token' => $token,
            'deviceId' => $deviceId
        ];

        if ($userType === 'student') {
            $userData['studentId'] = $user['student_id'];
            $userData['fullName'] = $user['full_name'];
            $userData['indexNumber'] = $user['index_number'];
        } else {
            $userData['lecturerId'] = $user['lecturer_id'];
            $userData['employeeId'] = $user['employee_id'];
            $userData['department'] = $user['department'];
        }

        return ['success' => true, 'message' => 'Login successful', 'user' => $userData];
    }

    /**
     * Check if device has an active session lock
     * 
     * @param int $studentId
     * @return array
     */
    private function checkDeviceLock($studentId) {
        $deviceId = generateDeviceId();
        
        // Check for active locks on this device for active sessions
        $stmt = $this->db->prepare("
            SELECT dsl.id, dsl.student_id, s.id as session_id
            FROM device_session_locks dsl
            JOIN attendance_sessions s ON dsl.session_id = s.id
            WHERE dsl.device_id = ? AND dsl.status = 'active' AND s.status = 'active'
            LIMIT 1
        ");
        $stmt->bind_param('s', $deviceId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $lock = $result->fetch_assoc();
            $stmt->close();
            
            // Check if locked to same student
            if ($lock['student_id'] != $studentId) {
                return [
                    'canProceed' => false,
                    'message' => 'This device is currently locked to another student for an active attendance session.'
                ];
            }
        }

        $stmt->close();
        return ['canProceed' => true];
    }

    /**
     * Verify authentication token
     * 
     * @param string $token
     * @return array|false
     */
    public function verifyToken($token) {
        $stmt = $this->db->prepare("
            SELECT us.user_id, us.device_id, u.user_type, u.email,
                   CASE 
                       WHEN u.user_type = 'student' THEN s.id
                       WHEN u.user_type = 'lecturer' THEN l.id
                       ELSE NULL
                   END as profile_id
            FROM user_sessions us
            JOIN users u ON us.user_id = u.id
            LEFT JOIN students s ON u.id = s.user_id
            LEFT JOIN lecturers l ON u.id = l.user_id
            WHERE us.token = ? AND us.expires_at > NOW()
        ");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return false;
        }

        $session = $result->fetch_assoc();
        $stmt->close();

        return $session;
    }

    /**
     * Logout user
     * NOTE: Device lock is NOT released on logout - only when session ends
     * 
     * @param string $token
     * @return array
     */
    public function logout($token) {
        $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE token = ?");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected > 0) {
            return ['success' => true, 'message' => 'Logout successful'];
        }

        return ['success' => false, 'message' => 'Logout failed'];
    }

    /**
     * Get current user data from token
     * 
     * @param string $token
     * @return array|false
     */
    public function getCurrentUser($token) {
        $session = $this->verifyToken($token);
        if (!$session) {
            return false;
        }

        $userId = $session['user_id'];
        $userType = $session['user_type'];

        if ($userType === 'student') {
            $stmt = $this->db->prepare("
                SELECT s.id, s.index_number, s.full_name, s.phone, u.email
                FROM students s
                JOIN users u ON s.user_id = u.id
                WHERE u.id = ?
            ");
        } else {
            $stmt = $this->db->prepare("
                SELECT l.id, l.employee_id, l.department, l.phone, u.email
                FROM lecturers l
                JOIN users u ON l.user_id = u.id
                WHERE u.id = ?
            ");
        }

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return false;
        }

        $userData = $result->fetch_assoc();
        $stmt->close();

        return ['userType' => $userType, 'data' => $userData];
    }

    /**
     * Create new lecturer account
     * 
     * @param string $fullName
     * @param string $email
     * @param string $employeeId
     * @param string $department
     * @param string $phone
     * @param string $password
     * @return array
     */
    public function createLecturer($fullName, $email, $employeeId, $department, $phone, $password) {
        // Validate inputs
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        if (strlen($fullName) < 2) {
            return ['success' => false, 'message' => 'Full name must be at least 2 characters'];
        }

        if (empty($employeeId)) {
            return ['success' => false, 'message' => 'Employee ID is required'];
        }

        // Check if email already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Email already registered'];
        }
        $stmt->close();

        // Check if employee ID already exists
        $stmt = $this->db->prepare("SELECT id FROM lecturers WHERE employee_id = ?");
        $stmt->bind_param('s', $employeeId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Employee ID already exists'];
        }
        $stmt->close();

        // Hash password
        $passwordHash = hashPassword($password);

        // Create user
        $stmt = $this->db->prepare("INSERT INTO users (email, password_hash, user_type) VALUES (?, ?, ?)");
        $userType = 'lecturer';
        $stmt->bind_param('sss', $email, $passwordHash, $userType);
        
        if (!$stmt->execute()) {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to create user account'];
        }
        
        $userId = $this->db->getConnection()->insert_id;
        $stmt->close();

        // Create lecturer profile
        $stmt = $this->db->prepare("INSERT INTO lecturers (user_id, employee_id, department, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('isss', $userId, $employeeId, $department, $phone);
        
        if (!$stmt->execute()) {
            // Rollback - delete user
            $this->db->prepare("DELETE FROM users WHERE id = ?")->bind_param('i', $userId)->execute();
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to create lecturer profile'];
        }
        
        $stmt->close();

        return ['success' => true, 'message' => 'Lecturer account created successfully'];
    }
}

?>

<?php
/**
 * Campus Track - Lecturer Management Handler
 * 
 * Handles course and student management
 */

require_once 'config.php';

class LecturerManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all courses for a lecturer
     * 
     * @param int $lecturerId
     * @return array
     */
    public function getCourses($lecturerId) {
        $stmt = $this->db->prepare("
            SELECT id, code, name, description, created_at
            FROM courses
            WHERE lecturer_id = ?
            ORDER BY code ASC
        ");

        $stmt->bind_param('i', $lecturerId);
        $stmt->execute();
        $result = $stmt->get_result();

        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }

        $stmt->close();
        return $courses;
    }

    /**
     * Create new course
     * 
     * @param int $lecturerId
     * @param string $code
     * @param string $name
     * @param string $description
     * @return array
     */
    public function createCourse($lecturerId, $code, $name, $description = '') {
        // Validate inputs
        if (strlen($code) < 2 || strlen($code) > 50) {
            return ['success' => false, 'message' => 'Course code must be 2-50 characters'];
        }

        if (strlen($name) < 2) {
            return ['success' => false, 'message' => 'Course name is required'];
        }

        // Check if code already exists
        $stmt = $this->db->prepare("SELECT id FROM courses WHERE code = ?");
        $stmt->bind_param('s', $code);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Course code already exists'];
        }
        $stmt->close();

        // Create course
        $stmt = $this->db->prepare(
            "INSERT INTO courses (lecturer_id, code, name, description) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('isss', $lecturerId, $code, $name, $description);

        if (!$stmt->execute()) {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to create course'];
        }

        $courseId = $this->db->getConnection()->insert_id;
        $stmt->close();

        return ['success' => true, 'message' => 'Course created successfully', 'courseId' => $courseId];
    }

    /**
     * Get all students in the system
     * 
     * @return array
     */
    public function getAllStudents() {
        $stmt = $this->db->prepare("
            SELECT 
                s.id,
                s.index_number,
                s.full_name,
                s.phone,
                u.email,
                s.created_at
            FROM students s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.full_name ASC
        ");

        $stmt->execute();
        $result = $stmt->get_result();

        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }

        $stmt->close();
        return $students;
    }

    /**
     * Add a student manually (admin action)
     * 
     * @param string $email
     * @param string $indexNumber
     * @param string $fullName
     * @param string $temporaryPassword
     * @return array
     */
    public function addStudent($email, $indexNumber, $fullName, $temporaryPassword) {
        // Validate inputs
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        if (strlen($indexNumber) < 2) {
            return ['success' => false, 'message' => 'Index number is required'];
        }

        if (strlen($fullName) < 2) {
            return ['success' => false, 'message' => 'Full name is required'];
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
        $passwordHash = hashPassword($temporaryPassword);

        // Create user
        $stmt = $this->db->prepare("INSERT INTO users (email, password_hash, user_type) VALUES (?, ?, ?)");
        $userType = 'student';
        $stmt->bind_param('sss', $email, $passwordHash, $userType);

        if (!$stmt->execute()) {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to create user'];
        }

        $userId = $this->db->getConnection()->insert_id;
        $stmt->close();

        // Create student profile
        $stmt = $this->db->prepare("INSERT INTO students (user_id, index_number, full_name) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $userId, $indexNumber, $fullName);

        if (!$stmt->execute()) {
            // Rollback
            $this->db->prepare("DELETE FROM users WHERE id = ?")->bind_param('i', $userId)->execute();
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to create student profile'];
        }

        $stmt->close();

        return [
            'success' => true,
            'message' => 'Student added successfully',
            'studentId' => $userId,
            'temporaryPassword' => $temporaryPassword
        ];
    }

    /**
     * Get student attendance summary
     * 
     * @param int $studentId
     * @return array
     */
    public function getStudentAttendanceSummary($studentId) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT s.id) as total_sessions,
                COUNT(DISTINCT ar.id) as sessions_attended,
                ROUND(COUNT(DISTINCT ar.id) / COUNT(DISTINCT s.id) * 100, 2) as attendance_percentage
            FROM attendance_sessions s
            LEFT JOIN attendance_records ar ON s.id = ar.session_id AND ar.student_id = ?
            WHERE s.status = 'closed'
        ");

        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary = $result->fetch_assoc();
        $stmt->close();

        return $summary;
    }
}

?>

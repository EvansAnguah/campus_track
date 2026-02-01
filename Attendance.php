<?php
/**
 * Campus Track - Attendance Handler
 * 
 * Handles session creation, attendance marking, device locking,
 * and attendance history retrieval
 */

require_once 'config.php';

class Attendance {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all active attendance sessions (for students)
     * 
     * @return array
     */
    public function getActiveSessions() {
        $stmt = $this->db->prepare("
            SELECT 
                s.id,
                c.code,
                c.name,
                l.employee_id,
                s.latitude,
                s.longitude,
                s.radius_meters,
                s.started_at,
                s.ended_at,
                s.status
            FROM attendance_sessions s
            JOIN courses c ON s.course_id = c.id
            JOIN lecturers l ON c.lecturer_id = l.id
            WHERE s.status = 'active' AND s.started_at <= NOW()
            ORDER BY s.started_at DESC
        ");

        $stmt->execute();
        $result = $stmt->get_result();
        $sessions = [];

        while ($row = $result->fetch_assoc()) {
            $sessions[] = $row;
        }

        $stmt->close();
        return $sessions;
    }

    /**
     * Mark attendance for a student in a session
     * 
     * @param int $sessionId
     * @param int $studentId
     * @param float $latitude
     * @param float $longitude
     * @return array
     */
    public function markAttendance($sessionId, $studentId, $latitude, $longitude) {
        $db = $this->db->getConnection();

        // 1. Verify session is active
        $stmt = $this->db->prepare("SELECT id, latitude, longitude, radius_meters, status FROM attendance_sessions WHERE id = ?");
        $stmt->bind_param('i', $sessionId);
        $stmt->execute();
        $sessionResult = $stmt->get_result();

        if ($sessionResult->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Session not found'];
        }

        $session = $sessionResult->fetch_assoc();
        $stmt->close();

        if ($session['status'] !== 'active') {
            return ['success' => false, 'message' => 'Session is not active'];
        }

        // 2. Calculate distance from session center
        $distance = calculateDistance(
            $session['latitude'],
            $session['longitude'],
            $latitude,
            $longitude
        );

        // 3. Verify student is within radius
        if ($distance > $session['radius_meters']) {
            return [
                'success' => false,
                'message' => 'You are ' . round($distance - $session['radius_meters'], 2) . 'm outside the attendance zone'
            ];
        }

        // 4. Check for duplicate attendance
        $stmt = $this->db->prepare("SELECT id FROM attendance_records WHERE session_id = ? AND student_id = ?");
        $stmt->bind_param('ii', $sessionId, $studentId);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'You have already marked attendance for this session'];
        }
        $stmt->close();

        // 5. Record attendance
        $stmt = $this->db->prepare(
            "INSERT INTO attendance_records (session_id, student_id, lat_recorded, long_recorded, distance_from_center) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('iiddd', $sessionId, $studentId, $latitude, $longitude, $distance);

        if (!$stmt->execute()) {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to mark attendance'];
        }

        $stmt->close();

        // 6. Lock device for this session
        $this->lockDeviceForSession($sessionId, $studentId);

        return ['success' => true, 'message' => 'Attendance marked successfully'];
    }

    /**
     * Lock device to student for active session
     * 
     * @param int $sessionId
     * @param int $studentId
     * @return void
     */
    private function lockDeviceForSession($sessionId, $studentId) {
        $deviceId = generateDeviceId();

        // Check if already locked (shouldn't happen, but safety check)
        $stmt = $this->db->prepare(
            "SELECT id FROM device_session_locks 
             WHERE session_id = ? AND device_id = ? AND status = 'active'"
        );
        $stmt->bind_param('is', $sessionId, $deviceId);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {
            $stmt->close();

            // Create new lock
            $stmt = $this->db->prepare(
                "INSERT INTO device_session_locks (session_id, student_id, device_id, status) 
                 VALUES (?, ?, ?, 'active')"
            );
            $stmt->bind_param('iis', $sessionId, $studentId, $deviceId);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt->close();
        }
    }

    /**
     * Get student's attendance history
     * 
     * @param int $studentId
     * @param string $period 'all', 'week', 'month'
     * @return array
     */
    public function getStudentAttendanceHistory($studentId, $period = 'all') {
        $dateFilter = '';

        switch ($period) {
            case 'week':
                $dateFilter = "AND s.started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $dateFilter = "AND s.started_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
        }

        $query = "
            SELECT 
                s.id as session_id,
                c.code,
                c.name,
                s.started_at,
                s.ended_at,
                s.status,
                CASE WHEN ar.id IS NOT NULL THEN 'attended' ELSE 'absent' END as attendance_status,
                ar.marked_at,
                ar.distance_from_center
            FROM attendance_sessions s
            JOIN courses c ON s.course_id = c.id
            LEFT JOIN attendance_records ar ON s.id = ar.session_id AND ar.student_id = ?
            WHERE 1=1 $dateFilter
            ORDER BY s.started_at DESC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }

        $stmt->close();
        return $history;
    }

    /**
     * Create new attendance session (lecturer only)
     * 
     * @param int $courseId
     * @param float $latitude
     * @param float $longitude
     * @param int $radiusMeters
     * @return array
     */
    public function createSession($courseId, $latitude, $longitude, $radiusMeters) {
        // Validate inputs
        if ($latitude < -90 || $latitude > 90) {
            return ['success' => false, 'message' => 'Invalid latitude'];
        }

        if ($longitude < -180 || $longitude > 180) {
            return ['success' => false, 'message' => 'Invalid longitude'];
        }

        if ($radiusMeters < 10 || $radiusMeters > 10000) {
            return ['success' => false, 'message' => 'Radius must be between 10m and 10km'];
        }

        // Verify course exists
        $stmt = $this->db->prepare("SELECT id FROM courses WHERE id = ?");
        $stmt->bind_param('i', $courseId);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Course not found'];
        }
        $stmt->close();

        // Create session
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare(
            "INSERT INTO attendance_sessions (course_id, latitude, longitude, radius_meters, status, started_at) 
             VALUES (?, ?, ?, ?, 'active', ?)"
        );
        $stmt->bind_param('iddis', $courseId, $latitude, $longitude, $radiusMeters, $now);

        if (!$stmt->execute()) {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to create session'];
        }

        $sessionId = $this->db->getConnection()->insert_id;
        $stmt->close();

        return [
            'success' => true,
            'message' => 'Session created successfully',
            'sessionId' => $sessionId
        ];
    }

    /**
     * End attendance session and release all device locks
     * 
     * @param int $sessionId
     * @return array
     */
    public function endSession($sessionId) {
        $db = $this->db->getConnection();

        // Update session status
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("UPDATE attendance_sessions SET status = 'closed', ended_at = ? WHERE id = ?");
        $stmt->bind_param('si', $now, $sessionId);

        if (!$stmt->execute()) {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to end session'];
        }
        $stmt->close();

        // Release all device locks for this session
        $stmt = $this->db->prepare("
            UPDATE device_session_locks 
            SET status = 'released', released_at = ?
            WHERE session_id = ? AND status = 'active'
        ");
        $stmt->bind_param('si', $now, $sessionId);
        $stmt->execute();
        $stmt->close();

        return ['success' => true, 'message' => 'Session ended and device locks released'];
    }

    /**
     * Get all sessions for a lecturer (with attendance counts)
     * 
     * @param int $lecturerId
     * @param string $period 'all', 'week', 'month'
     * @return array
     */
    public function getLecturerSessions($lecturerId, $period = 'all') {
        $dateFilter = '';

        switch ($period) {
            case 'week':
                $dateFilter = "AND s.started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $dateFilter = "AND s.started_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
        }

        $query = "
            SELECT 
                s.id,
                c.code,
                c.name,
                s.status,
                s.started_at,
                s.ended_at,
                COUNT(DISTINCT ar.id) as attendance_count,
                COUNT(DISTINCT st.id) as total_students
            FROM attendance_sessions s
            JOIN courses c ON s.course_id = c.id
            LEFT JOIN attendance_records ar ON s.id = ar.session_id
            CROSS JOIN (SELECT COUNT(*) as count FROM students) total_count
            CROSS JOIN (SELECT id FROM students) st
            WHERE c.lecturer_id = ? $dateFilter
            GROUP BY s.id
            ORDER BY s.started_at DESC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $lecturerId);
        $stmt->execute();
        $result = $stmt->get_result();

        $sessions = [];
        while ($row = $result->fetch_assoc()) {
            $sessions[] = $row;
        }

        $stmt->close();
        return $sessions;
    }

    /**
     * Get detailed attendance report for a session
     * 
     * @param int $sessionId
     * @return array
     */
    public function getSessionAttendanceReport($sessionId) {
        $stmt = $this->db->prepare("
            SELECT 
                st.id,
                st.index_number,
                st.full_name,
                CASE WHEN ar.id IS NOT NULL THEN 'attended' ELSE 'absent' END as status,
                ar.marked_at,
                ar.distance_from_center
            FROM students st
            LEFT JOIN attendance_records ar ON ar.student_id = st.id AND ar.session_id = ?
            ORDER BY st.full_name ASC
        ");

        $stmt->bind_param('i', $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();

        $report = [];
        while ($row = $result->fetch_assoc()) {
            $report[] = $row;
        }

        $stmt->close();
        return $report;
    }
}

?>

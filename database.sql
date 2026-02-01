-- Campus Track: Location-Based Attendance System
-- Database Schema
-- Created: 2026-02-01

-- =====================================================
-- USERS TABLE (Base table for all system users)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('student', 'lecturer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_user_type (user_type)
);

-- =====================================================
-- STUDENTS TABLE (Student-specific data)
-- =====================================================
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    index_number VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_index_number (index_number),
    INDEX idx_full_name (full_name)
);

-- =====================================================
-- LECTURERS TABLE (Lecturer-specific data)
-- =====================================================
CREATE TABLE IF NOT EXISTS lecturers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    employee_id VARCHAR(50) UNIQUE,
    department VARCHAR(255),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_employee_id (employee_id)
);

-- =====================================================
-- COURSES TABLE (Courses taught by lecturers)
-- =====================================================
CREATE TABLE IF NOT EXISTS courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lecturer_id INT NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lecturer_id) REFERENCES lecturers(id) ON DELETE CASCADE,
    INDEX idx_lecturer_id (lecturer_id),
    INDEX idx_code (code)
);

-- =====================================================
-- ATTENDANCE_SESSIONS TABLE (Lecturer-created sessions)
-- =====================================================
CREATE TABLE IF NOT EXISTS attendance_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    radius_meters INT NOT NULL,
    status ENUM('active', 'closed') DEFAULT 'active',
    started_at TIMESTAMP NOT NULL,
    ended_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_course_id (course_id),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at),
    INDEX idx_ended_at (ended_at)
);

-- =====================================================
-- ATTENDANCE_RECORDS TABLE (Student attendance marks)
-- =====================================================
CREATE TABLE IF NOT EXISTS attendance_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lat_recorded DECIMAL(10, 8) NOT NULL,
    long_recorded DECIMAL(11, 8) NOT NULL,
    distance_from_center DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (session_id, student_id),
    INDEX idx_session_id (session_id),
    INDEX idx_student_id (student_id),
    INDEX idx_marked_at (marked_at)
);

-- =====================================================
-- DEVICE_SESSION_LOCKS TABLE (Device locks during active sessions)
-- =====================================================
CREATE TABLE IF NOT EXISTS device_session_locks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    device_id VARCHAR(255) NOT NULL,
    locked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    released_at TIMESTAMP NULL,
    status ENUM('active', 'released') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_device_session (session_id, device_id),
    INDEX idx_session_id (session_id),
    INDEX idx_student_id (student_id),
    INDEX idx_device_id (device_id),
    INDEX idx_status (status)
);

-- =====================================================
-- USER_SESSIONS TABLE (Authentication session management)
-- =====================================================
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    device_id VARCHAR(255),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at),
    INDEX idx_device_id (device_id)
);

-- =====================================================
-- INDEXES FOR OPTIMIZATION
-- =====================================================
CREATE INDEX idx_sessions_active ON attendance_sessions(status, started_at);
CREATE INDEX idx_records_student_session ON attendance_records(student_id, session_id);

-- =====================================================
-- SAMPLE DATA (Optional - for testing)
-- =====================================================

-- Sample Lecturer Account
INSERT INTO users (email, password_hash, user_type) VALUES (
    'lecturer@campus.edu',
    '$2y$10$demo_hash_placeholder_do_not_use_in_production',
    'lecturer'
) ON DUPLICATE KEY UPDATE id = id;

-- Get the lecturer user ID and create lecturer profile
SET @lecturer_user_id = (SELECT id FROM users WHERE email = 'lecturer@campus.edu' LIMIT 1);

INSERT INTO lecturers (user_id, employee_id, department, phone) VALUES (
    @lecturer_user_id,
    'EMP001',
    'Computer Science',
    '+1234567890'
) ON DUPLICATE KEY UPDATE id = id;

-- Sample Student Accounts (DO NOT AUTO-CREATE - These are for reference only)
-- In production, use the registration interface

COMMIT;

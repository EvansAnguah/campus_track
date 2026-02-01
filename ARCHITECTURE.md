# Campus Track - Complete System Architecture

## 🏗️ System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                     CAMPUS TRACK SYSTEM                         │
│              Location-Based Attendance Platform                 │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                        FRONTEND LAYER                           │
│  (HTML/CSS/JavaScript - Runs in Student/Lecturer Browsers)     │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────┐  ┌──────────────────┐  ┌─────────────────┐
│  │  Landing Page   │  │  Student Dash    │  │  Lecturer Dash  │
│  │  (index.php)    │  │  (dashboard_     │  │  (dashboard_    │
│  │                 │  │   student.php)   │  │   lecturer.php) │
│  │  • Login Modal  │  │                  │  │                 │
│  │  • Register     │  │  • Active        │  │  • Create       │
│  │  • Features     │  │    Sessions      │  │    Sessions     │
│  │  • Hero Section │  │  • Mark Att.     │  │  • Monitor      │
│  │                 │  │  • History       │  │    Attendance   │
│  │                 │  │  • Profile       │  │  • Manage       │
│  │                 │  │                  │  │    Courses      │
│  │                 │  │                  │  │  • Add Students │
│  └─────────────────┘  └──────────────────┘  └─────────────────┘
│
│  ┌──────────────────────────────────────────────────────────────┐
│  │                  Frontend JavaScript Layer                   │
│  │  ┌────────────────┐  ┌──────────┐  ┌───────────────────────┐
│  │  │ Authentication │  │   API    │  │   Geolocation &      │
│  │  │   Module       │  │  Calls   │  │   Location Handling  │
│  │  │                │  │          │  │                       │
│  │  │ • Login        │  │ • Fetch  │  │ • Browser.geo.       │
│  │  │ • Register     │  │ • Error  │  │   getCurrentPosition │
│  │  │ • Logout       │  │   Handle │  │ • Distance calc      │
│  │  │ • Token Mgmt   │  │ • JSON   │  │ • Accuracy check     │
│  │  └────────────────┘  └──────────┘  └───────────────────────┘
│  └──────────────────────────────────────────────────────────────┘
│
│  ┌──────────────────────────────────────────────────────────────┐
│  │                    Styling Layer (CSS)                       │
│  │  ┌──────────────────┐  ┌──────────────────────────────────┐
│  │  │ Main Stylesheet  │  │  Dashboard Stylesheet           │
│  │  │ (style.css)      │  │  (dashboard.css)                │
│  │  │                  │  │                                  │
│  │  │ • Responsive     │  │ • Sidebar nav                   │
│  │  │ • Dark mode vars │  │ • Cards & tables                │
│  │  │ • Animations     │  │ • Forms & modals                │
│  │  │ • Colors & theme │  │ • Mobile adapts                 │
│  │  │ • 800+ lines     │  │ • 400+ lines                    │
│  │  └──────────────────┘  └──────────────────────────────────┘
│  └──────────────────────────────────────────────────────────────┘
└─────────────────────────────────────────────────────────────────┘
                              │
                         HTTP/REST
                         (POST JSON)
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      BACKEND LAYER                              │
│  (PHP - Runs on Web Server)                                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────────────────────────────────────────────────────┐
│  │              Configuration & Database Layer                  │
│  │  ┌──────────────────────────────────────────────────────────┐
│  │  │  config.php                                              │
│  │  │  • DB Connection (singleton pattern)                     │
│  │  │  • Constants (timeout, costs, emails)                    │
│  │  │  • Helper Functions:                                     │
│  │  │    - hashPassword() → bcrypt hashing                     │
│  │  │    - verifyPassword() → bcrypt verification              │
│  │  │    - generateToken() → 32-byte random                    │
│  │  │    - generateDeviceId() → browser fingerprint            │
│  │  │    - calculateDistance() → Haversine formula             │
│  │  │    - sendJSON() → response formatting                    │
│  │  │    - cleanupExpiredSessions() → maintenance              │
│  │  └──────────────────────────────────────────────────────────┘
│  └──────────────────────────────────────────────────────────────┘
│
│  ┌──────────────────────────────────────────────────────────────┐
│  │              Authentication System                           │
│  │  ┌──────────────────────────────────────────────────────────┐
│  │  │  Auth.php (300+ lines)                                   │
│  │  │                                                           │
│  │  │  Public Methods:                                          │
│  │  │  ├─ registerStudent(email, pwd, index, name)             │
│  │  │  │  └─ Validates & hashes, creates user & profile       │
│  │  │  │                                                        │
│  │  │  ├─ login(identifier, pwd, userType)                     │
│  │  │  │  ├─ Finds user by email (lecturer) or index (student)│
│  │  │  │  ├─ Verifies password                                 │
│  │  │  │  ├─ Checks device lock status                         │
│  │  │  │  ├─ Generates session token                           │
│  │  │  │  └─ Creates user_sessions entry                       │
│  │  │  │                                                        │
│  │  │  ├─ verifyToken(token) → session data                   │
│  │  │  │  └─ Checks expiration & validity                      │
│  │  │  │                                                        │
│  │  │  ├─ logout(token)                                        │
│  │  │  │  └─ Deletes user_sessions entry                       │
│  │  │  │     (Note: Device lock persists!)                     │
│  │  │  │                                                        │
│  │  │  ├─ getCurrentUser(token) → user profile                │
│  │  │  │  └─ Retrieves user details                            │
│  │  │  │                                                        │
│  │  │  └─ checkDeviceLock(studentId) → permission             │
│  │  │     └─ Prevents multi-student fraud                      │
│  │  │                                                           │
│  │  └──────────────────────────────────────────────────────────┘
│  └──────────────────────────────────────────────────────────────┘
│
│  ┌──────────────────────────────────────────────────────────────┐
│  │              Attendance System                               │
│  │  ┌──────────────────────────────────────────────────────────┐
│  │  │  Attendance.php (400+ lines)                             │
│  │  │                                                           │
│  │  │  Public Methods:                                          │
│  │  │  ├─ getActiveSessions() → all active sessions            │
│  │  │  │                                                        │
│  │  │  ├─ markAttendance(sessionId, studentId, lat, lon)      │
│  │  │  │  ├─ Verify session active                             │
│  │  │  │  ├─ Calculate distance (Haversine)                    │
│  │  │  │  ├─ Check within radius                               │
│  │  │  │  ├─ Prevent duplicate                                 │
│  │  │  │  ├─ Record attendance                                 │
│  │  │  │  └─ Lock device (via private method)                  │
│  │  │  │                                                        │
│  │  │  ├─ getStudentAttendanceHistory(studentId, period)      │
│  │  │  │  └─ Filter: all/week/month                            │
│  │  │  │                                                        │
│  │  │  ├─ createSession(courseId, lat, lon, radius)           │
│  │  │  │  ├─ Validate coordinates & radius                     │
│  │  │  │  ├─ Verify course exists                              │
│  │  │  │  └─ Insert into attendance_sessions                   │
│  │  │  │                                                        │
│  │  │  ├─ endSession(sessionId)                                │
│  │  │  │  ├─ Set status = 'closed'                             │
│  │  │  │  └─ Release ALL device locks (CRITICAL)              │
│  │  │  │                                                        │
│  │  │  ├─ getLecturerSessions(lecturerId, period)             │
│  │  │  │  └─ With attendance counts                             │
│  │  │  │                                                        │
│  │  │  └─ getSessionAttendanceReport(sessionId)               │
│  │  │     └─ All students + status                             │
│  │  │                                                           │
│  │  └──────────────────────────────────────────────────────────┘
│  └──────────────────────────────────────────────────────────────┘
│
│  ┌──────────────────────────────────────────────────────────────┐
│  │              Lecturer Management System                      │
│  │  ┌──────────────────────────────────────────────────────────┐
│  │  │  LecturerManager.php (250+ lines)                        │
│  │  │                                                           │
│  │  │  Public Methods:                                          │
│  │  │  ├─ getCourses(lecturerId)                               │
│  │  │  │  └─ All courses by lecturer                           │
│  │  │  │                                                        │
│  │  │  ├─ createCourse(lecturerId, code, name, desc)          │
│  │  │  │  └─ Validate & prevent duplicates                     │
│  │  │  │                                                        │
│  │  │  ├─ getAllStudents()                                     │
│  │  │  │  └─ List all students in system                       │
│  │  │  │                                                        │
│  │  │  ├─ addStudent(email, index, name, tempPwd)             │
│  │  │  │  └─ Manual student creation by admin                  │
│  │  │  │                                                        │
│  │  │  └─ getStudentAttendanceSummary(studentId)              │
│  │  │     └─ Total & attended sessions, percentage             │
│  │  │                                                           │
│  │  └──────────────────────────────────────────────────────────┘
│  └──────────────────────────────────────────────────────────────┘
│
│  ┌──────────────────────────────────────────────────────────────┐
│  │              Email Notification System                       │
│  │  ┌──────────────────────────────────────────────────────────┐
│  │  │  EmailNotifier.php (180+ lines)                          │
│  │  │                                                           │
│  │  │  Public Methods:                                          │
│  │  │  ├─ sendSessionStartAlert(...)                           │
│  │  │  │  └─ HTML email on session creation                    │
│  │  │  │                                                        │
│  │  │  ├─ sendAttendanceConfirmation(...)                      │
│  │  │  │  └─ HTML email on attendance mark                     │
│  │  │  │                                                        │
│  │  │  ├─ sendPasswordReset(...)                               │
│  │  │  │  └─ Reset link email                                  │
│  │  │  │                                                        │
│  │  │  ├─ sendSessionReminder(...)                             │
│  │  │  │  └─ Reminder before session                           │
│  │  │  │                                                        │
│  │  │  └─ send(to, subject, message) [PRIVATE]                │
│  │  │     └─ Uses PHP mail() function                          │
│  │  │                                                           │
│  │  └──────────────────────────────────────────────────────────┘
│  └──────────────────────────────────────────────────────────────┘
│
│  ┌──────────────────────────────────────────────────────────────┐
│  │              API Endpoint Layer                              │
│  │  ┌──────────────────────────────────────────────────────────┐
│  │  │  api_auth.php - Authentication Endpoints                 │
│  │  │  ├─ register → Student registration                      │
│  │  │  ├─ login → User login (student/lecturer)                │
│  │  │  ├─ logout → User logout                                 │
│  │  │  ├─ verify → Token verification                          │
│  │  │  └─ current-user → User profile data                     │
│  │  │                                                           │
│  │  │  api_attendance.php - Attendance Endpoints               │
│  │  │  ├─ get-active-sessions → List sessions                  │
│  │  │  ├─ mark-attendance → Record attendance                  │
│  │  │  ├─ student-history → Attendance history                 │
│  │  │  ├─ create-session → Create new session                  │
│  │  │  ├─ end-session → End session & release locks            │
│  │  │  ├─ lecturer-sessions → Lecturer's sessions              │
│  │  │  └─ session-report → Attendance report                   │
│  │  │                                                           │
│  │  │  api_lecturer.php - Lecturer Management                  │
│  │  │  ├─ get-courses → List lecturer's courses                │
│  │  │  ├─ create-course → Create new course                    │
│  │  │  ├─ get-students → List all students                     │
│  │  │  ├─ add-student → Add student manually                   │
│  │  │  └─ student-summary → Attendance summary                 │
│  │  │                                                           │
│  │  └──────────────────────────────────────────────────────────┘
│  └──────────────────────────────────────────────────────────────┘
└─────────────────────────────────────────────────────────────────┘
                              │
                        SQL Queries
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     DATABASE LAYER                              │
│  (MySQL/MariaDB)                                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐
│  │                   CORE TABLES                              │
│  │                                                            │
│  │  users                  attendance_sessions               │
│  │  ├─ id (PK)            ├─ id (PK)                         │
│  │  ├─ email (UNIQUE)     ├─ course_id (FK)                  │
│  │  ├─ password_hash      ├─ latitude                        │
│  │  ├─ user_type          ├─ longitude                       │
│  │  └─ timestamps         ├─ radius_meters                   │
│  │                         ├─ status (active/closed)         │
│  │  students              └─ timestamps                       │
│  │  ├─ id (PK)                                               │
│  │  ├─ user_id (FK)       attendance_records                 │
│  │  ├─ index_number       ├─ id (PK)                         │
│  │  ├─ full_name          ├─ session_id (FK)                 │
│  │  └─ phone              ├─ student_id (FK)                 │
│  │                         ├─ lat/long_recorded               │
│  │  lecturers             ├─ distance_from_center             │
│  │  ├─ id (PK)            ├─ marked_at                       │
│  │  ├─ user_id (FK)       └─ UNIQUE(session, student)        │
│  │  ├─ employee_id                                           │
│  │  ├─ department         device_session_locks               │
│  │  └─ phone              ├─ id (PK)                         │
│  │                         ├─ session_id (FK)                 │
│  │  courses               ├─ student_id (FK)                 │
│  │  ├─ id (PK)            ├─ device_id (fingerprint)         │
│  │  ├─ lecturer_id (FK)   ├─ status (active/released)        │
│  │  ├─ code (UNIQUE)      └─ UNIQUE(session, device)         │
│  │  ├─ name                                                  │
│  │  └─ description        user_sessions                      │
│  │                         ├─ id (PK)                         │
│  │                         ├─ user_id (FK)                    │
│  │                         ├─ token (UNIQUE)                  │
│  │                         ├─ device_id                       │
│  │                         ├─ ip_address                      │
│  │                         ├─ expires_at (1 hour)             │
│  │                         └─ timestamps                       │
│  │                                                            │
│  └────────────────────────────────────────────────────────────┘
│
│  ┌────────────────────────────────────────────────────────────┐
│  │              Indexes & Constraints                         │
│  │  ├─ 20+ Indexes for query optimization                    │
│  │  ├─ 4 Foreign Keys for referential integrity              │
│  │  ├─ 6 Unique Constraints for data uniqueness              │
│  │  └─ Automatic timestamp management                        │
│  └────────────────────────────────────────────────────────────┘
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Request/Response Flow for Attendance Marking

```
STUDENT BROWSER                      WEB SERVER                  DATABASE
     │                                   │                           │
     │   1. POST /api_attendance.php      │                           │
     │      action=mark-attendance        │                           │
     │      {token, sessionId, lat, lon}  │                           │
     ├──────────────────────────────────→ │                           │
     │                                    │                           │
     │                                    │  2. Parse request         │
     │                                    │     Load classes          │
     │                                    │                           │
     │                                    │  3. Verify token          │
     │                                    ├──────────────────────────→
     │                                    │   SELECT FROM user_sessions
     │                                    │   WHERE token = ? AND     │
     │                                    │   expires_at > NOW()      │
     │                                    │←──────────────────────────┤
     │                                    │   Token valid ✓           │
     │                                    │                           │
     │                                    │  4. Get session details   │
     │                                    ├──────────────────────────→
     │                                    │   SELECT * FROM           │
     │                                    │   attendance_sessions     │
     │                                    │   WHERE id = ?            │
     │                                    │←──────────────────────────┤
     │                                    │   Session: lat, lon, rad  │
     │                                    │                           │
     │                                    │  5. Calculate distance    │
     │                                    │     (Haversine formula)   │
     │                                    │     distance = 42m        │
     │                                    │     radius = 50m          │
     │                                    │     ✓ Within radius       │
     │                                    │                           │
     │                                    │  6. Check duplicate       │
     │                                    ├──────────────────────────→
     │                                    │   SELECT id FROM          │
     │                                    │   attendance_records      │
     │                                    │   WHERE session_id = ? AND│
     │                                    │   student_id = ?          │
     │                                    │←──────────────────────────┤
     │                                    │   No record ✓             │
     │                                    │                           │
     │                                    │  7. Insert attendance     │
     │                                    ├──────────────────────────→
     │                                    │   INSERT INTO             │
     │                                    │   attendance_records      │
     │                                    │   (session_id, student_id,│
     │                                    │    lat, lon, distance)    │
     │                                    │←──────────────────────────┤
     │                                    │   Record ID: 1234         │
     │                                    │                           │
     │                                    │  8. Lock device           │
     │                                    ├──────────────────────────→
     │                                    │   INSERT INTO             │
     │                                    │   device_session_locks    │
     │                                    │   (session_id, student_id,│
     │                                    │    device_id, status)     │
     │                                    │←──────────────────────────┤
     │                                    │   Lock created ✓          │
     │                                    │                           │
     │                                    │  9. Send email            │
     │                                    │     EmailNotifier→        │
     │                                    │     mail()                │
     │                                    │                           │
     │                                    │  10. Return success       │
     │ {"success": true, "message":       │                           │
     │  "Attendance marked successfully"}│                           │
     │←────────────────────────────────── │                           │
     │                                    │                           │
     │  11. Frontend update               │                           │
     │      Show alert ✓                  │                           │
     │      Reload active sessions        │                           │
```

---

## 🔐 Device Locking Mechanism

```
ACTIVE SESSION FLOW:

Time 1: Student 1 Logs In (Device A)
  ┌─────────────────────────────┐
  │ POST login (Device A)        │
  │ Student ID: 101              │
  │ Session ID: 5 (ACTIVE)       │
  └─────────────────────────────┘
           │
           ▼
  ✓ Device lock created:
  ├─ session_id: 5
  ├─ student_id: 101
  ├─ device_id: abc123hash
  ├─ status: active
  └─ Device A now LOCKED to Student 101

Time 2: Student 1 Marks Attendance (Device A)
  ┌─────────────────────────────┐
  │ Mark Attendance (Device A)   │
  │ Device lock active ✓         │
  └─────────────────────────────┘
           │
           ▼
  ✓ Attendance recorded
  ✓ Device lock PERSISTS

Time 3: Student 1 Logs Out (Device A)
  ┌─────────────────────────────┐
  │ POST logout (Device A)       │
  │ Token deleted from DB        │
  └─────────────────────────────┘
           │
           ▼
  ✓ Session ended
  ✗ Device lock STILL ACTIVE
    └─ This is crucial! Prevents fraud

Time 4: Student 2 Tries Login (Device A)
  ┌─────────────────────────────┐
  │ POST login (Device A)        │
  │ Student ID: 202              │
  │ Session ID: 5 (still ACTIVE) │
  └─────────────────────────────┘
           │
           ▼
  Check device locks:
  ├─ Device A has lock?
  │  └─ YES: session_id=5, student_id=101
  ├─ Same student as login attempt?
  │  └─ NO: 202 ≠ 101
  └─ Session still active?
     └─ YES: status='active'
           │
           ▼
  ✗ LOGIN BLOCKED
  "This device is currently locked to another
   student for an active attendance session."

Time 5: Different Device (Device B) - No Problem
  ┌─────────────────────────────┐
  │ POST login (Device B)        │
  │ Student ID: 202              │
  │ Session ID: 5 (still ACTIVE) │
  └─────────────────────────────┘
           │
           ▼
  Check device locks:
  ├─ Device B has lock?
  │  └─ NO (different device)
  │
  ▼
  ✓ LOGIN SUCCEEDS
  ✓ New lock created: device_id=xyz789hash

Time 6: Lecturer Ends Session
  ┌─────────────────────────────┐
  │ Lecturer: End Session        │
  │ Session ID: 5                │
  └─────────────────────────────┘
           │
           ▼
  Update attendance_sessions:
  ├─ id = 5
  ├─ status: active → closed
  └─ ended_at: NOW()

  Update device_session_locks:
  ├─ All locks WHERE session_id = 5
  ├─ status: active → released
  └─ released_at: NOW()
           │
           ▼
  ✓ ALL device locks for session 5 RELEASED
  ✓ Device A now FREE for new logins
  ✓ Device B now FREE for new logins

Time 7: Student 1 or 2 Can Now Login Normally
  ┌─────────────────────────────┐
  │ POST login (Device A)        │
  │ Student ID: 202              │
  │ Session ID: 5 (now CLOSED)   │
  └─────────────────────────────┘
           │
           ▼
  Check device locks:
  ├─ Device A has active lock?
  │  └─ NO (all locks released)
  │
  ▼
  ✓ LOGIN SUCCEEDS
  ✓ New lock created for new session (if any)
```

---

## 🛡️ Security Layers

```
┌─────────────────────────────────────────────────────────────┐
│              SECURITY ARCHITECTURE                          │
└─────────────────────────────────────────────────────────────┘

Layer 1: Authentication
├─ Bcrypt hashing (10 cost)
├─ Random 32-byte session tokens
├─ Token expiration (1 hour)
├─ Device fingerprinting
└─ Browser permission checks

Layer 2: Session Management
├─ Session tokens in database (not cookies)
├─ Automatic cleanup of expired sessions
├─ Per-request token verification
├─ Device lock persistence
└─ Logout doesn't release device lock

Layer 3: Attendance Validation
├─ Server-side ONLY (no client trust)
├─ Haversine formula (GPS accuracy)
├─ Radius validation before recording
├─ Duplicate prevention (unique constraint)
└─ Session status verification

Layer 4: Device Locking
├─ Browser fingerprint generation
├─ Persistent locks during active session
├─ One student per device rule
├─ Automatic release on session end
└─ Cannot bypass (survives logout/refresh)

Layer 5: Database Security
├─ Prepared statements (SQL injection)
├─ Foreign key constraints
├─ Unique constraints (duplicate prevention)
├─ Proper indexes (query optimization)
└─ No sensitive data in logs

Layer 6: API Security
├─ JSON-only communication
├─ Token verification on all endpoints
├─ Role-based access control
├─ Input validation & sanitization
└─ Error handling (no sensitive info leaked)
```

---

## 📊 Data Flow Diagram

```
USER INTERACTION → FRONTEND → API → BACKEND LOGIC → DATABASE
                  (Browser)  (HTTP) (PHP Classes)  (MySQL)

Student Registration:
  Fill form → Validate → POST /api_auth.php → Auth.php → users + students tables

Student Login:
  Enter credentials → POST /api_auth.php → Verify password → Create session token →
  user_sessions table

Attendance Marking:
  Click button → Request location → POST /api_attendance.php →
  Server validates → Haversine calculation → Record attendance →
  Lock device → Send email → attendance_records + device_session_locks tables

Session Viewing:
  Load dashboard → GET /api_attendance.php → Query sessions → Display UI

Session Management:
  Lecturer creates → POST create-session → attendance_sessions table
  Lecturer ends → POST end-session → Release device locks →
  device_session_locks table updated
```

---

**Architecture Version:** 1.0  
**Last Updated:** February 2026  
**Status:** Production-Ready

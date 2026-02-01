/**
 * Campus Track - Main JavaScript
 * 
 * Handles frontend interactions, authentication, and attendance marking
 */

// ============================================
// CONFIGURATION
// ============================================
const API_BASE = '/api_';
const STORAGE_KEY = 'campus_track_session';
let currentUser = null;
let currentToken = null;

// ============================================
// INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is already logged in
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored) {
        try {
            const session = JSON.parse(stored);
            currentUser = session.user;
            currentToken = session.token;
            // Verify token is still valid
            verifyToken();
        } catch (e) {
            localStorage.removeItem(STORAGE_KEY);
        }
    }
});

// ============================================
// AUTHENTICATION
// ============================================

function showLoginModal(userType) {
    const modal = document.getElementById('loginModal');
    const container = document.getElementById('loginContainer');

    const html = userType === 'student' 
        ? getStudentLoginHTML() 
        : getLecturerLoginHTML();

    container.innerHTML = html;
    modal.classList.add('show');
}

function closeLoginModal() {
    document.getElementById('loginModal').classList.remove('show');
    document.getElementById('loginContainer').innerHTML = '';
}

function getStudentLoginHTML() {
    return `
        <div class="login-form">
            <h2>Student Login</h2>
            <p>Enter your credentials to access Campus Track</p>
            <div class="form-group">
                <label>Index Number</label>
                <input type="text" id="studentIndex" placeholder="e.g., STU001" />
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="studentPassword" placeholder="Enter your password" />
            </div>
            <button class="btn btn-primary btn-block" onclick="loginStudent()">Login</button>
            <p style="text-align: center; margin-top: 20px; color: #7f8c8d;">
                Don't have an account? <a href="#" onclick="showStudentRegister()">Register here</a>
            </p>
        </div>
    `;
}

function getLecturerLoginHTML() {
    return `
        <div class="login-form">
            <h2>Lecturer Login</h2>
            <p>Manage attendance sessions and track student participation</p>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" id="lecturerEmail" placeholder="your@email.com" />
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="lecturerPassword" placeholder="Enter your password" />
            </div>
            <button class="btn btn-primary btn-block" onclick="loginLecturer()">Login</button>
        </div>
    `;
}

function loginStudent() {
    const index = document.getElementById('studentIndex').value;
    const password = document.getElementById('studentPassword').value;

    if (!index || !password) {
        showAlert('Please fill in all fields', 'error');
        return;
    }

    const data = {
        identifier: index,
        password: password,
        userType: 'student'
    };

    apiCall('auth.php?action=login', data)
        .then(response => {
            if (response.success) {
                // Store session
                localStorage.setItem(STORAGE_KEY, JSON.stringify({
                    user: response.user,
                    token: response.user.token
                }));
                currentUser = response.user;
                currentToken = response.user.token;
                closeLoginModal();
                redirectToDashboard();
            } else {
                showAlert(response.message, 'error');
            }
        })
        .catch(error => {
            showAlert('Login failed. Please try again.', 'error');
        });
}

function loginLecturer() {
    const email = document.getElementById('lecturerEmail').value;
    const password = document.getElementById('lecturerPassword').value;

    if (!email || !password) {
        showAlert('Please fill in all fields', 'error');
        return;
    }

    const data = {
        identifier: email,
        password: password,
        userType: 'lecturer'
    };

    apiCall('auth.php?action=login', data)
        .then(response => {
            if (response.success) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify({
                    user: response.user,
                    token: response.user.token
                }));
                currentUser = response.user;
                currentToken = response.user.token;
                closeLoginModal();
                redirectToDashboard();
            } else {
                showAlert(response.message, 'error');
            }
        })
        .catch(error => {
            showAlert('Login failed. Please try again.', 'error');
        });
}

function showStudentRegister() {
    const container = document.getElementById('loginContainer');
    container.innerHTML = `
        <div class="login-form">
            <h2>Student Registration</h2>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" id="regFullName" placeholder="John Doe" />
            </div>
            <div class="form-group">
                <label>Index Number</label>
                <input type="text" id="regIndexNumber" placeholder="STU001" />
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="regEmail" placeholder="john@email.com" />
            </div>
            <div class="form-group">
                <label>Password (min 8 characters)</label>
                <input type="password" id="regPassword" placeholder="Create a strong password" />
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" id="regPasswordConfirm" placeholder="Confirm your password" />
            </div>
            <button class="btn btn-primary btn-block" onclick="registerStudent()">Register</button>
            <p style="text-align: center; margin-top: 20px; color: #7f8c8d;">
                Already have an account? <a href="#" onclick="showLoginModal('student')">Login here</a>
            </p>
        </div>
    `;
}

function registerStudent() {
    const fullName = document.getElementById('regFullName').value;
    const indexNumber = document.getElementById('regIndexNumber').value;
    const email = document.getElementById('regEmail').value;
    const password = document.getElementById('regPassword').value;
    const passwordConfirm = document.getElementById('regPasswordConfirm').value;

    if (!fullName || !indexNumber || !email || !password || !passwordConfirm) {
        showAlert('Please fill in all fields', 'error');
        return;
    }

    const data = {
        fullName: fullName,
        indexNumber: indexNumber,
        email: email,
        password: password,
        passwordConfirm: passwordConfirm
    };

    apiCall('auth.php?action=register', data)
        .then(response => {
            if (response.success) {
                showAlert('Registration successful! Please login.', 'success');
                setTimeout(() => showLoginModal('student'), 1500);
            } else {
                showAlert(response.message, 'error');
            }
        })
        .catch(error => {
            showAlert('Registration failed. Please try again.', 'error');
        });
}

function verifyToken() {
    if (!currentToken) return;

    const data = { token: currentToken };

    apiCall('auth.php?action=verify', data)
        .then(response => {
            if (!response.success) {
                logout();
            }
        })
        .catch(error => {
            logout();
        });
}

function logout() {
    localStorage.removeItem(STORAGE_KEY);
    currentUser = null;
    currentToken = null;
    window.location.href = '/index.php';
}

function redirectToDashboard() {
    if (currentUser.userType === 'student') {
        window.location.href = '/dashboard_student.php';
    } else {
        window.location.href = '/dashboard_lecturer.php';
    }
}

// ============================================
// API HELPER
// ============================================

function apiCall(endpoint, data = {}) {
    return fetch(API_BASE + endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    });
}

// ============================================
// ALERTS & NOTIFICATIONS
// ============================================

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;

    // Find or create alerts container
    let container = document.getElementById('alertsContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'alertsContainer';
        container.style.position = 'fixed';
        container.style.top = '80px';
        container.style.right = '20px';
        container.style.zIndex = '999';
        container.style.maxWidth = '400px';
        document.body.appendChild(container);
    }

    container.appendChild(alertDiv);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// ============================================
// GEOLOCATION
// ============================================

function getLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject('Geolocation is not supported by this browser');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            position => {
                resolve({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                });
            },
            error => {
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        reject('Location permission denied. Please enable location access.');
                        break;
                    case error.POSITION_UNAVAILABLE:
                        reject('Location information is unavailable.');
                        break;
                    case error.TIMEOUT:
                        reject('Location request timed out.');
                        break;
                    default:
                        reject('An unknown error occurred while getting location.');
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });
}

// ============================================
// STUDENT DASHBOARD
// ============================================

function markAttendance(sessionId) {
    if (!currentToken) {
        showAlert('Session expired. Please login again.', 'error');
        return;
    }

    // Request location
    getLocation()
        .then(location => {
            const data = {
                token: currentToken,
                sessionId: sessionId,
                latitude: location.latitude,
                longitude: location.longitude
            };

            return apiCall('attendance.php?action=mark-attendance', data);
        })
        .then(response => {
            if (response.success) {
                showAlert('Attendance marked successfully!', 'success');
                setTimeout(() => loadActiveSessions(), 1500);
            } else {
                showAlert(response.message, 'error');
            }
        })
        .catch(error => {
            showAlert(error, 'error');
        });
}

function loadActiveSessions() {
    if (!currentToken) return;

    const data = { token: currentToken };

    apiCall('attendance.php?action=get-active-sessions', data)
        .then(response => {
            if (response.success && response.sessions) {
                displayActiveSessions(response.sessions);
            }
        })
        .catch(error => {
            console.error('Error loading sessions:', error);
        });
}

function displayActiveSessions(sessions) {
    const container = document.getElementById('activeSessions');
    if (!container) return;

    if (sessions.length === 0) {
        container.innerHTML = '<p>No active attendance sessions at the moment.</p>';
        return;
    }

    const html = sessions.map(session => `
        <div class="session-card">
            <div class="session-header">
                <h3>${session.code}: ${session.name}</h3>
                <span class="session-status">ACTIVE</span>
            </div>
            <div class="session-details">
                <p><strong>Lecturer:</strong> ${session.employee_id}</p>
                <p><strong>Radius:</strong> ${session.radius_meters}m</p>
                <p><strong>Started:</strong> ${new Date(session.started_at).toLocaleTimeString()}</p>
            </div>
            <button class="btn btn-primary btn-block" onclick="markAttendance(${session.id})">
                Mark Attendance
            </button>
        </div>
    `).join('');

    container.innerHTML = html;
}

function viewAttendanceHistory() {
    if (!currentToken) return;

    const period = document.getElementById('historyPeriod')?.value || 'all';
    const data = {
        token: currentToken,
        period: period
    };

    apiCall('attendance.php?action=student-history', data)
        .then(response => {
            if (response.success && response.history) {
                displayAttendanceHistory(response.history);
            }
        })
        .catch(error => {
            showAlert('Error loading history', 'error');
        });
}

function displayAttendanceHistory(history) {
    const container = document.getElementById('attendanceHistory');
    if (!container) return;

    const html = history.map(record => {
        const status = record.attendance_status === 'attended' 
            ? '<span class="status-indicator status-attended"></span>Attended' 
            : '<span class="status-indicator status-absent"></span>Absent';

        return `
            <tr>
                <td>${record.code}</td>
                <td>${record.name}</td>
                <td>${new Date(record.started_at).toLocaleDateString()}</td>
                <td>${status}</td>
            </tr>
        `;
    }).join('');

    container.innerHTML = html;
}

// ============================================
// LECTURER DASHBOARD
// ============================================

function loadLecturerCourses() {
    if (!currentToken) return;

    const data = { token: currentToken };

    apiCall('lecturer.php?action=get-courses', data)
        .then(response => {
            if (response.success && response.courses) {
                displayCourses(response.courses);
            }
        })
        .catch(error => {
            showAlert('Error loading courses', 'error');
        });
}

function displayCourses(courses) {
    const select = document.getElementById('courseSelect');
    if (!select) return;

    const options = courses.map(course => 
        `<option value="${course.id}">${course.code}: ${course.name}</option>`
    ).join('');

    select.innerHTML = `<option value="">Select a course...</option>${options}`;
}

function createNewCourse() {
    const code = document.getElementById('newCourseCode').value;
    const name = document.getElementById('newCourseName').value;
    const description = document.getElementById('newCourseDesc').value;

    if (!code || !name) {
        showAlert('Course code and name are required', 'error');
        return;
    }

    const data = {
        token: currentToken,
        code: code,
        name: name,
        description: description
    };

    apiCall('lecturer.php?action=create-course', data)
        .then(response => {
            if (response.success) {
                showAlert('Course created successfully', 'success');
                document.getElementById('newCourseCode').value = '';
                document.getElementById('newCourseName').value = '';
                document.getElementById('newCourseDesc').value = '';
                loadLecturerCourses();
            } else {
                showAlert(response.message, 'error');
            }
        })
        .catch(error => {
            showAlert('Error creating course', 'error');
        });
}

function createAttendanceSession() {
    const courseId = document.getElementById('courseSelect').value;
    const lat = document.getElementById('sessionLat').value;
    const lon = document.getElementById('sessionLon').value;
    const radius = document.getElementById('sessionRadius').value;

    if (!courseId || !lat || !lon || !radius) {
        showAlert('Please fill in all session details', 'error');
        return;
    }

    const data = {
        token: currentToken,
        courseId: parseInt(courseId),
        latitude: parseFloat(lat),
        longitude: parseFloat(lon),
        radiusMeters: parseInt(radius)
    };

    apiCall('attendance.php?action=create-session', data)
        .then(response => {
            if (response.success) {
                showAlert('Session created and is now active!', 'success');
                document.getElementById('courseSelect').value = '';
                document.getElementById('sessionLat').value = '';
                document.getElementById('sessionLon').value = '';
                document.getElementById('sessionRadius').value = '';
            } else {
                showAlert(response.message, 'error');
            }
        })
        .catch(error => {
            showAlert('Error creating session', 'error');
        });
}

// ============================================
// UTILITY
// ============================================

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

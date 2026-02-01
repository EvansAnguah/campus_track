<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - Campus Track</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <?php
        // Verify user is logged in (client-side verification happens in JS)
    ?>

    <nav class="navbar">
        <div class="navbar-content">
            <div class="logo">
                <h1>🎓 Campus Track</h1>
            </div>
            <div class="nav-links">
                <span id="userDisplay">Welcome, Lecturer</span>
                <button class="btn btn-secondary" onclick="logout()">Logout</button>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-menu">
                <div class="menu-item active" onclick="switchView('create-session')">
                    <span class="menu-icon">➕</span>
                    <span>Create Session</span>
                </div>
                <div class="menu-item" onclick="switchView('my-sessions')">
                    <span class="menu-icon">📋</span>
                    <span>My Sessions</span>
                </div>
                <div class="menu-item" onclick="switchView('courses')">
                    <span class="menu-icon">📚</span>
                    <span>Manage Courses</span>
                </div>
                <div class="menu-item" onclick="switchView('students')">
                    <span class="menu-icon">👥</span>
                    <span>Students</span>
                </div>
                <div class="menu-item" onclick="switchView('profile')">
                    <span class="menu-icon">👤</span>
                    <span>My Profile</span>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Create Session View -->
            <div id="create-session" class="view-section active">
                <h2>Create Attendance Session</h2>
                <p class="section-subtitle">Set up a new attendance session for your course</p>

                <div class="form-card">
                    <div class="form-group">
                        <label>Course</label>
                        <select id="courseSelect" class="form-control">
                            <option value="">Loading courses...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Latitude</label>
                        <input type="text" id="sessionLat" placeholder="e.g., 40.7128" />
                        <small>Use your device's GPS or maps</small>
                    </div>

                    <div class="form-group">
                        <label>Longitude</label>
                        <input type="text" id="sessionLon" placeholder="e.g., -74.0060" />
                    </div>

                    <div class="form-group">
                        <label>Allowed Radius (meters)</label>
                        <input type="number" id="sessionRadius" placeholder="50" min="10" max="10000" />
                        <small>Minimum 10m, Maximum 10km</small>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary btn-block" onclick="createAttendanceSession()">Start Session</button>
                        <button class="btn btn-secondary btn-block" onclick="useCurrentLocation()">Use My Current Location</button>
                    </div>
                </div>
            </div>

            <!-- My Sessions View -->
            <div id="my-sessions" class="view-section">
                <h2>My Sessions</h2>

                <div class="filter-section">
                    <label>Filter by:</label>
                    <select class="form-control" onchange="filterSessions()">
                        <option value="all">All Time</option>
                        <option value="week">Last 7 Days</option>
                        <option value="month">Last 30 Days</option>
                    </select>
                </div>

                <div id="sessionsList" class="sessions-list">
                    <div class="spinner"></div>
                </div>
            </div>

            <!-- Courses View -->
            <div id="courses" class="view-section">
                <h2>Manage Courses</h2>

                <div class="form-card">
                    <h3>Add New Course</h3>
                    <div class="form-group">
                        <label>Course Code</label>
                        <input type="text" id="newCourseCode" placeholder="e.g., CS101" />
                    </div>
                    <div class="form-group">
                        <label>Course Name</label>
                        <input type="text" id="newCourseName" placeholder="e.g., Introduction to Programming" />
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="newCourseDesc" placeholder="Course description (optional)"></textarea>
                    </div>
                    <button class="btn btn-primary btn-block" onclick="createNewCourse()">Create Course</button>
                </div>

                <div class="courses-list" style="margin-top: 30px;">
                    <h3>Your Courses</h3>
                    <div id="coursesList" class="table-responsive">
                        <table class="courses-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody id="coursesTableBody">
                                <tr><td colspan="3" class="text-center">Loading courses...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Students View -->
            <div id="students" class="view-section">
                <h2>Students</h2>

                <div class="form-card">
                    <h3>Add Student Manually</h3>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="addStudentName" placeholder="John Doe" />
                    </div>
                    <div class="form-group">
                        <label>Index Number</label>
                        <input type="text" id="addStudentIndex" placeholder="STU001" />
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="addStudentEmail" placeholder="john@email.com" />
                    </div>
                    <button class="btn btn-primary btn-block" onclick="addStudentManual()">Add Student</button>
                </div>

                <div class="students-list" style="margin-top: 30px;">
                    <h3>All Students</h3>
                    <div id="studentsList" class="table-responsive">
                        <table class="students-table">
                            <thead>
                                <tr>
                                    <th>Index Number</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody id="studentsTableBody">
                                <tr><td colspan="4" class="text-center">Loading students...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Profile View -->
            <div id="profile" class="view-section">
                <h2>My Profile</h2>
                
                <div class="profile-card">
                    <div class="profile-field">
                        <label>Email</label>
                        <p id="profileEmail">-</p>
                    </div>
                    <div class="profile-field">
                        <label>Employee ID</label>
                        <p id="profileEmployeeId">-</p>
                    </div>
                    <div class="profile-field">
                        <label>Department</label>
                        <p id="profileDepartment">-</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
    <script>
        // Check if logged in
        const stored = localStorage.getItem('campus_track_session');
        if (!stored) {
            window.location.href = '/index.php';
        }

        const session = JSON.parse(stored);
        currentUser = session.user;
        currentToken = session.token;

        // Verify is lecturer
        if (currentUser.userType !== 'lecturer') {
            window.location.href = '/dashboard_student.php';
        }

        // Update display
        document.getElementById('userDisplay').textContent = 'Welcome, ' + (currentUser.employeeId || 'Lecturer');

        // Load initial data
        loadLecturerCourses();
        updateCoursesTable();
        loadAllStudents();
        loadLecturerSessions();
        updateProfileDisplay();

        function switchView(viewId) {
            document.querySelectorAll('.view-section').forEach(el => {
                el.classList.remove('active');
            });
            document.getElementById(viewId).classList.add('active');

            document.querySelectorAll('.menu-item').forEach(el => {
                el.classList.remove('active');
            });
            event.target.closest('.menu-item').classList.add('active');

            if (viewId === 'my-sessions') {
                loadLecturerSessions();
            } else if (viewId === 'courses') {
                updateCoursesTable();
            } else if (viewId === 'students') {
                loadAllStudents();
            }
        }

        function updateProfileDisplay() {
            document.getElementById('profileEmail').textContent = currentUser.email;
            document.getElementById('profileEmployeeId').textContent = currentUser.employeeId || 'N/A';
            document.getElementById('profileDepartment').textContent = currentUser.department || 'N/A';
        }

        function updateCoursesTable() {
            loadLecturerCourses();
        }

        function loadLecturerSessions() {
            const data = { token: currentToken, period: 'all' };
            apiCall('attendance.php?action=lecturer-sessions', data)
                .then(response => {
                    if (response.success && response.sessions) {
                        displayLecturerSessions(response.sessions);
                    }
                })
                .catch(error => {
                    document.getElementById('sessionsList').innerHTML = '<p>Error loading sessions</p>';
                });
        }

        function displayLecturerSessions(sessions) {
            const container = document.getElementById('sessionsList');
            if (sessions.length === 0) {
                container.innerHTML = '<p>No sessions yet. Create one to get started!</p>';
                return;
            }

            const html = sessions.map(session => `
                <div class="session-card">
                    <div class="session-header">
                        <h3>${session.code}: ${session.name}</h3>
                        <span class="session-status ${session.status}">${session.status.toUpperCase()}</span>
                    </div>
                    <div class="session-details">
                        <p><strong>Attendance:</strong> ${session.attendance_count} / ${session.total_students}</p>
                        <p><strong>Started:</strong> ${new Date(session.started_at).toLocaleString()}</p>
                    </div>
                    ${session.status === 'active' ? `
                        <button class="btn btn-danger" onclick="endSession(${session.id})">End Session</button>
                    ` : ''}
                </div>
            `).join('');

            container.innerHTML = html;
        }

        function endSession(sessionId) {
            if (confirm('Are you sure you want to end this session? Device locks will be released.')) {
                const data = {
                    token: currentToken,
                    sessionId: sessionId
                };

                apiCall('attendance.php?action=end-session', data)
                    .then(response => {
                        if (response.success) {
                            showAlert('Session ended successfully', 'success');
                            loadLecturerSessions();
                        } else {
                            showAlert(response.message, 'error');
                        }
                    })
                    .catch(error => {
                        showAlert('Error ending session', 'error');
                    });
            }
        }

        function filterSessions() {
            loadLecturerSessions();
        }

        function useCurrentLocation() {
            getLocation()
                .then(location => {
                    document.getElementById('sessionLat').value = location.latitude.toFixed(6);
                    document.getElementById('sessionLon').value = location.longitude.toFixed(6);
                    showAlert('Location loaded successfully', 'success');
                })
                .catch(error => {
                    showAlert(error, 'error');
                });
        }

        function loadAllStudents() {
            const data = { token: currentToken };
            apiCall('lecturer.php?action=get-students', data)
                .then(response => {
                    if (response.success && response.students) {
                        displayAllStudents(response.students);
                    }
                })
                .catch(error => {
                    document.getElementById('studentsTableBody').innerHTML = '<tr><td colspan="4">Error loading students</td></tr>';
                });
        }

        function displayAllStudents(students) {
            const tbody = document.getElementById('studentsTableBody');
            if (students.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">No students registered yet</td></tr>';
                return;
            }

            const html = students.map(student => `
                <tr>
                    <td>${student.index_number}</td>
                    <td>${student.full_name}</td>
                    <td>${student.email}</td>
                    <td>${new Date(student.created_at).toLocaleDateString()}</td>
                </tr>
            `).join('');

            tbody.innerHTML = html;
        }

        function addStudentManual() {
            const name = document.getElementById('addStudentName').value;
            const index = document.getElementById('addStudentIndex').value;
            const email = document.getElementById('addStudentEmail').value;

            if (!name || !index || !email) {
                showAlert('Please fill in all fields', 'error');
                return;
            }

            const data = {
                token: currentToken,
                fullName: name,
                indexNumber: index,
                email: email
            };

            apiCall('lecturer.php?action=add-student', data)
                .then(response => {
                    if (response.success) {
                        showAlert('Student added successfully. Temporary password: ' + response.temporaryPassword, 'success');
                        document.getElementById('addStudentName').value = '';
                        document.getElementById('addStudentIndex').value = '';
                        document.getElementById('addStudentEmail').value = '';
                        loadAllStudents();
                    } else {
                        showAlert(response.message, 'error');
                    }
                })
                .catch(error => {
                    showAlert('Error adding student', 'error');
                });
        }
    </script>
</body>
</html>

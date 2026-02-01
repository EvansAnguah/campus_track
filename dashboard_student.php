<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Campus Track</title>
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
                <span id="userDisplay">Welcome, Student</span>
                <button class="btn btn-secondary" onclick="logout()">Logout</button>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-menu">
                <div class="menu-item active" onclick="switchView('active-sessions')">
                    <span class="menu-icon">📍</span>
                    <span>Mark Attendance</span>
                </div>
                <div class="menu-item" onclick="switchView('history')">
                    <span class="menu-icon">📊</span>
                    <span>Attendance History</span>
                </div>
                <div class="menu-item" onclick="switchView('profile')">
                    <span class="menu-icon">👤</span>
                    <span>My Profile</span>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Mark Attendance View -->
            <div id="active-sessions" class="view-section active">
                <h2>Mark Attendance</h2>
                <p class="section-subtitle">Active attendance sessions - Click "Mark Attendance" when you're at the location</p>
                
                <div id="activeSessions" class="sessions-container">
                    <div class="spinner"></div>
                </div>
            </div>

            <!-- History View -->
            <div id="history" class="view-section">
                <h2>Attendance History</h2>
                
                <div class="filter-section">
                    <label>Filter by period:</label>
                    <select id="historyPeriod" class="form-control" onchange="viewAttendanceHistory()">
                        <option value="all">All Time</option>
                        <option value="month">Last 30 Days</option>
                        <option value="week">Last 7 Days</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceHistory">
                            <tr><td colspan="4" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Profile View -->
            <div id="profile" class="view-section">
                <h2>My Profile</h2>
                
                <div class="profile-card">
                    <div class="profile-field">
                        <label>Full Name</label>
                        <p id="profileFullName">-</p>
                    </div>
                    <div class="profile-field">
                        <label>Index Number</label>
                        <p id="profileIndexNumber">-</p>
                    </div>
                    <div class="profile-field">
                        <label>Email</label>
                        <p id="profileEmail">-</p>
                    </div>
                    <div class="profile-field">
                        <label>Member Since</label>
                        <p id="profileCreatedAt">-</p>
                    </div>
                </div>

                <div class="profile-actions">
                    <button class="btn btn-primary" onclick="showChangePassword()">Change Password</button>
                </div>

                <div id="changePasswordForm" class="hidden" style="margin-top: 30px;">
                    <h3>Change Password</h3>
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" id="currentPassword" placeholder="Enter current password" />
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" id="newPassword" placeholder="Enter new password" />
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" id="confirmNewPassword" placeholder="Confirm new password" />
                    </div>
                    <button class="btn btn-primary" onclick="updatePassword()">Update Password</button>
                    <button class="btn btn-secondary" onclick="hideChangePassword()">Cancel</button>
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

        // Update display
        document.getElementById('userDisplay').textContent = 'Welcome, ' + currentUser.fullName;
        
        // Load initial data
        loadActiveSessions();
        loadStudentProfile();

        function switchView(viewId) {
            // Hide all views
            document.querySelectorAll('.view-section').forEach(el => {
                el.classList.remove('active');
            });
            // Show selected view
            document.getElementById(viewId).classList.add('active');

            // Update menu
            document.querySelectorAll('.menu-item').forEach(el => {
                el.classList.remove('active');
            });
            event.target.closest('.menu-item').classList.add('active');

            // Load view-specific data
            if (viewId === 'history') {
                viewAttendanceHistory();
            }
        }

        function loadStudentProfile() {
            document.getElementById('profileFullName').textContent = currentUser.fullName;
            document.getElementById('profileIndexNumber').textContent = currentUser.indexNumber;
            document.getElementById('profileEmail').textContent = currentUser.email;
            document.getElementById('profileCreatedAt').textContent = new Date().toLocaleDateString();
        }

        function showChangePassword() {
            document.getElementById('changePasswordForm').classList.remove('hidden');
        }

        function hideChangePassword() {
            document.getElementById('changePasswordForm').classList.add('hidden');
        }

        function updatePassword() {
            const current = document.getElementById('currentPassword').value;
            const newPass = document.getElementById('newPassword').value;
            const confirm = document.getElementById('confirmNewPassword').value;

            if (!current || !newPass || !confirm) {
                showAlert('Please fill in all fields', 'error');
                return;
            }

            if (newPass !== confirm) {
                showAlert('Passwords do not match', 'error');
                return;
            }

            showAlert('Feature coming soon', 'info');
        }

        // Refresh active sessions every 30 seconds
        setInterval(loadActiveSessions, 30000);
    </script>
</body>
</html>

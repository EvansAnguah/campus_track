<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Lecturer Account - Campus Track</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="logo">
                <h1>🎓 Campus Track</h1>
            </div>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Back to Home</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding: 60px 20px;">
        <div style="max-width: 500px; margin: 0 auto;">
            <h2 style="text-align: center; margin-bottom: 30px;">Create Lecturer Account</h2>
            
            <div id="alertsContainer" style="margin-bottom: 20px;"></div>

            <div class="form-card">
                <form id="lecturerForm">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="lecturerFullName" placeholder="Dr. John Smith" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" id="lecturerEmail" placeholder="john@university.edu" required>
                    </div>

                    <div class="form-group">
                        <label>Employee ID</label>
                        <input type="text" id="employeeId" placeholder="EMP001" required>
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" id="department" placeholder="Computer Science" required>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" id="phone" placeholder="+1234567890">
                    </div>

                    <div class="form-group">
                        <label>Password (min 8 characters)</label>
                        <input type="password" id="lecturerPassword" placeholder="Create a strong password" required>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" id="lecturerPasswordConfirm" placeholder="Confirm your password" required>
                    </div>

                    <button type="button" class="btn btn-primary btn-block" onclick="createLecturerAccount()">
                        Create Account
                    </button>

                    <p style="text-align: center; margin-top: 20px; color: #7f8c8d;">
                        Already have an account? <a href="index.php">Login here</a>
                    </p>
                </form>
            </div>

            <div class="about" style="margin-top: 40px; background: white; padding: 20px; border: 1px solid var(--border-color); border-radius: 8px;">
                <h3 style="color: var(--secondary-color); margin-bottom: 15px;">Important Information</h3>
                <ul style="color: var(--text-secondary); line-height: 1.8;">
                    <li>This page is for administrator use only</li>
                    <li>Enter accurate department information</li>
                    <li>The employee ID must be unique</li>
                    <li>Password must be at least 8 characters</li>
                    <li>After account creation, the lecturer can login immediately</li>
                </ul>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Campus Track. All rights reserved.</p>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        function showAlert(message, type = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;

            let container = document.getElementById('alertsContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'alertsContainer';
                document.body.insertBefore(container, document.body.firstChild);
            }

            container.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        function createLecturerAccount() {
            const fullName = document.getElementById('lecturerFullName').value;
            const email = document.getElementById('lecturerEmail').value;
            const employeeId = document.getElementById('employeeId').value;
            const department = document.getElementById('department').value;
            const phone = document.getElementById('phone').value;
            const password = document.getElementById('lecturerPassword').value;
            const passwordConfirm = document.getElementById('lecturerPasswordConfirm').value;

            // Validate
            if (!fullName || !email || !employeeId || !department || !password || !passwordConfirm) {
                showAlert('Please fill in all required fields', 'error');
                return;
            }

            if (password !== passwordConfirm) {
                showAlert('Passwords do not match', 'error');
                return;
            }

            if (password.length < 8) {
                showAlert('Password must be at least 8 characters', 'error');
                return;
            }

            const data = {
                fullName: fullName,
                email: email,
                employeeId: employeeId,
                department: department,
                phone: phone,
                password: password,
                passwordConfirm: passwordConfirm
            };

            apiCall('auth.php?action=create-lecturer', data)
                .then(response => {
                    if (response.success) {
                        showAlert('Lecturer account created successfully! They can now login.', 'success');
                        document.getElementById('lecturerForm').reset();
                        setTimeout(() => {
                            window.location.href = 'index.php';
                        }, 2000);
                    } else {
                        showAlert(response.message || 'Failed to create lecturer account', 'error');
                    }
                })
                .catch(error => {
                    showAlert(error.message || 'Error creating lecturer account', 'error');
                    console.error('Error:', error);
                });
        }
    </script>
</body>
</html>

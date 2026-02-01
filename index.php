<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Track - Location-Based Attendance System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="landing-page">
        <!-- Navigation -->
        <nav class="navbar">
            <div class="navbar-content">
                <div class="logo">
                    <h1>🎓 Campus Track</h1>
                </div>
                <div class="nav-links">
                    <a href="#features" class="nav-link">Features</a>
                    <a href="#about" class="nav-link">About</a>
                    <div class="nav-buttons">
                        <button class="btn btn-secondary" onclick="showLoginModal('student')">Student Login</button>
                        <button class="btn btn-primary" onclick="showLoginModal('lecturer')">Lecturer Login</button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h2>Smart Attendance Through Location</h2>
                <p>A modern, secure, and location-based attendance system for tertiary institutions</p>
                <div class="cta-buttons">
                    <button class="btn btn-primary btn-large" onclick="showLoginModal('student')">Get Started as Student</button>
                    <button class="btn btn-secondary btn-large" onclick="showLoginModal('lecturer')">Manage as Lecturer</button>
                </div>
            </div>
            <div class="hero-animation">
                <div class="animated-circle circle-1"></div>
                <div class="animated-circle circle-2"></div>
                <div class="animated-circle circle-3"></div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="features">
            <h2>Key Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📍</div>
                    <h3>GPS-Based Verification</h3>
                    <p>Students can mark attendance only when physically present within the defined zone</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Device Locking</h3>
                    <p>One device, one student per active session - prevents fraud and unauthorized access</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Real-Time Reports</h3>
                    <p>Lecturers see live attendance with historical reports filtered by date ranges</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📧</div>
                    <h3>Email Alerts</h3>
                    <p>Automated notifications for session starts and attendance confirmations</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔐</div>
                    <h3>Enterprise Security</h3>
                    <p>Server-side validation, hashed passwords, session tokens, and comprehensive logging</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Simple & Fast</h3>
                    <p>Clean interface designed for quick attendance marking in busy academic environments</p>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="how-it-works">
            <h2>How It Works</h2>
            <div class="workflow">
                <div class="workflow-step">
                    <div class="step-number">1</div>
                    <h3>Lecturer Creates Session</h3>
                    <p>Define course, GPS location, and allowed radius</p>
                </div>
                <div class="workflow-arrow">→</div>
                <div class="workflow-step">
                    <div class="step-number">2</div>
                    <h3>Session Goes Live</h3>
                    <p>Students receive email notification</p>
                </div>
                <div class="workflow-arrow">→</div>
                <div class="workflow-step">
                    <div class="step-number">3</div>
                    <h3>Student Marks Attendance</h3>
                    <p>Location verified, attendance recorded</p>
                </div>
                <div class="workflow-arrow">→</div>
                <div class="workflow-step">
                    <div class="step-number">4</div>
                    <h3>Device Locked</h3>
                    <p>Prevents duplicate or fraudulent entries</p>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="about">
            <h2>About Campus Track</h2>
            <div class="about-content">
                <p>
                    Campus Track is a enterprise-grade attendance management system designed specifically 
                    for tertiary institutions. It combines modern geolocation technology with proven security 
                    practices to create a simple yet effective solution.
                </p>
                <ul class="about-list">
                    <li><strong>Secure:</strong> All authentication and attendance validation happens server-side</li>
                    <li><strong>Simple:</strong> Intuitive interface requires minimal training</li>
                    <li><strong>Realistic:</strong> Works with browser APIs only - no special hardware needed</li>
                    <li><strong>Explainable:</strong> Clear feedback at every step of the process</li>
                </ul>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; 2026 Campus Track. All rights reserved.</p>
            <p>A secure location-based attendance system for academic institutions</p>
        </footer>
    </div>

    <!-- Login Modal (Student) -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeLoginModal()">&times;</span>
            <div id="loginContainer"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
</body>
</html>


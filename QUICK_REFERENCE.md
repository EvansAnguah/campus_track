# Campus Track - Quick Reference Guide

## 📁 Complete File List

### Core Application Files

| File | Purpose | Lines |
|------|---------|-------|
| `index.php` | Landing page with login/register modals | ~150 |
| `dashboard_student.php` | Student dashboard interface | ~200 |
| `dashboard_lecturer.php` | Lecturer dashboard interface | ~250 |

### Backend System (PHP)

| File | Purpose | Lines |
|------|---------|-------|
| `config.php` | Database connection & configuration | ~150 |
| `Auth.php` | Authentication & session management | ~300 |
| `Attendance.php` | Attendance logic & GPS validation | ~400 |
| `LecturerManager.php` | Course & student management | ~250 |
| `EmailNotifier.php` | Email notification system | ~180 |

### API Endpoints (PHP)

| File | Purpose | Lines |
|------|---------|-------|
| `api_auth.php` | Authentication API endpoints | ~120 |
| `api_attendance.php` | Attendance API endpoints | ~200 |
| `api_lecturer.php` | Lecturer management API endpoints | ~150 |

### Database

| File | Purpose |
|------|---------|
| `database.sql` | Database schema with 8 tables |

### Frontend Assets

| File | Purpose | Lines |
|------|---------|-------|
| `assets/css/style.css` | Main stylesheet | ~800 |
| `assets/css/dashboard.css` | Dashboard styles | ~400 |
| `assets/js/main.js` | Frontend logic & API calls | ~600 |

### Documentation

| File | Purpose |
|------|---------|
| `README.md` | Main project documentation |
| `SETUP.md` | Installation & setup guide |
| `IMPLEMENTATION_SUMMARY.md` | Project completion summary |

**Total Project:** ~17 files, ~4,500+ lines of code

---

## 🔧 Quick Setup (5 Minutes)

### Step 1: Import Database
```bash
1. Open phpMyAdmin
2. Create database: campus_track
3. Import database.sql
```

### Step 2: Update Config
```php
# Edit config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'campus_track');
```

### Step 3: Access Application
```
http://localhost/campus_track/index.php
```

### Step 4: Test Login
```
Lecturer Email: lecturer@campus.edu
(Set password in database or phpMyAdmin)
```

---

## 🎯 Core Workflows

### Student Workflow
```
1. Register (or pre-created by lecturer)
2. Login with index number + password
3. Dashboard shows active sessions
4. Click "Mark Attendance"
5. Allow browser location permission
6. System validates GPS position
7. Attendance recorded if within radius
8. Email confirmation sent
9. View history anytime
```

### Lecturer Workflow
```
1. Login with email + password
2. Create courses (one-time)
3. Create attendance session:
   - Select course
   - Enter GPS coordinates
   - Set radius
   - Click "Start Session"
4. Session immediately active
5. Monitor real-time attendance
6. End session when done
7. All device locks released
8. View detailed reports
```

### Device Locking Workflow
```
Active Session:
- First student login → Device locked to that student
- Logout → Device still locked (crucial!)
- Second student tries login → BLOCKED
- Different device → Works fine (separate lock)

Session Ends:
- Lecturer clicks "End Session"
- All device locks for that session released
- Students can now logout/login normally
```

---

## 📊 Database Schema Quick Reference

### Users Table
```sql
- id (PK)
- email (UNIQUE)
- password_hash
- user_type (student/lecturer)
- created_at
```

### Students Table
```sql
- id (PK)
- user_id (FK → users)
- index_number (UNIQUE)
- full_name
- phone
```

### Lecturers Table
```sql
- id (PK)
- user_id (FK → users)
- employee_id
- department
- phone
```

### Courses Table
```sql
- id (PK)
- lecturer_id (FK)
- code (UNIQUE)
- name
- description
```

### Attendance_Sessions Table
```sql
- id (PK)
- course_id (FK)
- latitude
- longitude
- radius_meters
- status (active/closed)
- started_at
- ended_at
```

### Attendance_Records Table
```sql
- id (PK)
- session_id (FK) \
- student_id (FK) / → UNIQUE (prevents duplicates)
- lat_recorded
- long_recorded
- distance_from_center
- marked_at
```

### Device_Session_Locks Table
```sql
- id (PK)
- session_id (FK)
- student_id (FK)
- device_id (browser fingerprint)
- locked_at
- status (active/released)
- UNIQUE (session_id, device_id)
```

### User_Sessions Table
```sql
- id (PK)
- user_id (FK)
- token (UNIQUE)
- device_id
- ip_address
- expires_at (1 hour)
```

---

## 🔌 API Endpoints Summary

### Authentication
```
POST /api_auth.php?action=register
  Input: email, password, indexNumber, fullName
  
POST /api_auth.php?action=login
  Input: identifier, password, userType (student/lecturer)
  
POST /api_auth.php?action=logout
  Input: token
  
POST /api_auth.php?action=verify
  Input: token
```

### Attendance
```
POST /api_attendance.php?action=get-active-sessions
  Input: token
  
POST /api_attendance.php?action=mark-attendance
  Input: token, sessionId, latitude, longitude
  
POST /api_attendance.php?action=student-history
  Input: token, period (all/week/month)
  
POST /api_attendance.php?action=create-session
  Input: token, courseId, latitude, longitude, radiusMeters
  
POST /api_attendance.php?action=end-session
  Input: token, sessionId
```

### Lecturer
```
POST /api_lecturer.php?action=get-courses
  Input: token
  
POST /api_lecturer.php?action=create-course
  Input: token, code, name, description
  
POST /api_lecturer.php?action=get-students
  Input: token
  
POST /api_lecturer.php?action=add-student
  Input: token, email, indexNumber, fullName
```

---

## 🛡️ Security Summary

| Feature | Implementation |
|---------|-----------------|
| Passwords | Bcrypt (10 cost) |
| Sessions | 32-byte random tokens, 1 hour expiry |
| SQL Injection | Prepared statements everywhere |
| GPS Validation | Server-side Haversine formula |
| Device Locking | Browser fingerprint + database |
| Duplicate Attendance | Unique constraint (session_id, student_id) |
| Cross-site | Token verification on all endpoints |

---

## 📱 Browser Compatibility

| Browser | Support | Notes |
|---------|---------|-------|
| Chrome | ✅ Full | All features work |
| Firefox | ✅ Full | All features work |
| Safari | ✅ Full | iOS 13+ for geolocation |
| Edge | ✅ Full | All features work |
| IE 11 | ❌ No | Not supported |

---

## 🌍 GPS Coordinates Guide

### Getting Coordinates

**From Google Maps:**
1. Right-click location on map
2. Click the coordinates (they copy automatically)
3. Paste into session form

**From Browser:**
1. Click "Use My Current Location" button
2. Allow location permission
3. Coordinates auto-fill

### Sample Coordinates

| Location | Latitude | Longitude | Typical Radius |
|----------|----------|-----------|-----------------|
| Classroom | 40.7128 | -74.0060 | 50m |
| Building Entrance | 40.7129 | -74.0061 | 100m |
| Campus Center | 40.7130 | -74.0062 | 300m |
| Lecture Hall | 40.7131 | -74.0063 | 50m |

---

## 🐛 Troubleshooting Quick Guide

| Problem | Solution |
|---------|----------|
| Database connection fails | Check DB credentials in config.php |
| Geolocation not working | Allow browser permission, check HTTPS in production |
| Email not sending | Verify PHP mail() configured, check SENDER_EMAIL |
| Login fails | Clear browser cache, verify user exists in database |
| Attendance marking fails | Check student is within GPS radius, session is active |
| Device lock error | Wait for session to end, or logout then end session |
| No active sessions showing | Check lecturer has created session and it's active |

---

## 📈 Performance Tips

- Sessions in database expire after 1 hour (auto-cleanup)
- Add indexes to large queries
- Use Redis for session storage if high traffic
- Archive old attendance data (90+ days)
- Monitor database size monthly

---

## 🔐 Production Checklist

- [ ] Change default passwords
- [ ] Enable HTTPS (SSL certificate)
- [ ] Configure email service properly
- [ ] Set file permissions (644/755)
- [ ] Regular database backups
- [ ] Monitor error logs
- [ ] Update PHP to latest version
- [ ] Configure rate limiting
- [ ] Test with real GPS data
- [ ] Load test with multiple concurrent users

---

## 📞 Support Resources

1. **README.md** - Full documentation
2. **SETUP.md** - Installation guide
3. **IMPLEMENTATION_SUMMARY.md** - Technical overview
4. **This file** - Quick reference

---

## 📝 Notes

- Device locking is the KEY security feature
- Always end session before testing new one
- Students can only mark attendance once per session
- Email configuration optional (falls back to no emails)
- All validation happens server-side (no client-side trust)
- GPS accuracy varies by device (typically 5-20m)

---

**Last Updated:** February 2026  
**Version:** 1.0.0  
**Status:** Production Ready

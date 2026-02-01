# Campus Track Installation & Setup Guide

## Quick Start

### 1. Database Setup

1. Open **phpMyAdmin** (typically at `http://localhost/phpmyadmin`)
2. Click "New" to create a new database
3. Name it: `campus_track`
4. Click "Create"
5. Select the `campus_track` database
6. Click "Import" tab
7. Browse and select `database.sql`
8. Click "Import"

**Expected Tables After Import:**
- users
- students
- lecturers
- courses
- attendance_sessions
- attendance_records
- device_session_locks
- user_sessions

### 2. File Configuration

Edit `config.php` and update:

```php
define('DB_HOST', 'localhost');      // Your MySQL host
define('DB_USER', 'root');           // MySQL username
define('DB_PASS', '');               // MySQL password
define('DB_NAME', 'campus_track');   // Database name
```

### 3. Email Configuration (Optional)

Edit `config.php`:

```php
define('SENDER_EMAIL', 'noreply@yourschool.edu');
define('SENDER_NAME', 'Campus Track');
```

Ensure PHP mail is configured on your server.

### 4. Start Using

1. Navigate to: `http://localhost/campus_track/index.php` (or your domain)
2. Click "Student Login" to register a new student account
3. Or login as lecturer (see test credentials below)

---

## Test Credentials

### Lecturer Account (Pre-created in Database)

**Email:** `lecturer@campus.edu`  
**Password:** Change this in production!

**Steps to Login:**
1. Click "Lecturer Login" button
2. Enter email: `lecturer@campus.edu`
3. Enter password: (check with admin)

**First Actions as Lecturer:**
1. Go to "Manage Courses"
2. Add sample courses (e.g., CS101, MATH201)
3. Go to "Create Session"
4. Create a test attendance session

---

## Student Registration

### Create Test Student Account

1. Click "Student Login" → "Register here"
2. Fill in:
   - **Full Name:** John Doe
   - **Index Number:** STU001
   - **Email:** john@email.com
   - **Password:** Test@1234 (min 8 chars)
3. Click "Register"
4. Login with index number and password

---

## Testing Attendance Marking

### Prerequisites
- Student account created
- Active session created by lecturer
- GPS/Geolocation enabled in browser
- Browser running on device with location access

### Test Flow

1. **As Lecturer:**
   - Go to "Create Session"
   - Select course
   - Enter GPS coordinates (use current location button)
   - Set radius: 50m
   - Click "Start Session"

2. **As Student:**
   - Login to student account
   - Dashboard shows active sessions
   - Click "Mark Attendance"
   - Browser requests location permission → Click "Allow"
   - System validates location
   - If within radius → Attendance marked!
   - Email notification sent

3. **Verify Attendance:**
   - As Lecturer: Go to "My Sessions" → see attendance count
   - As Student: Go to "Attendance History" → see marked attendance

---

## Device Locking Test

### How It Works

1. Student 1 logs in on Device A during active session → Device locked to Student 1
2. Student 1 logs out → Device still locked (NOT released)
3. Student 2 tries to login on Device A → ERROR: Device locked to Student 1
4. Lecturer ends session → All device locks released
5. Now Student 2 can login on Device A normally

### Test Steps

1. Open browser on Device A / Session A
2. Login as Student 1
3. Mark attendance (device locked)
4. **Logout**
5. **Try to login as Student 2** → See error message
6. Go to another device / browser (Device B)
7. Login as Student 2 → Works fine (different device)
8. Back to Device A: Session still active (Student 1's device lock still active)
9. Lecturer ends session
10. Now Device A can login as Student 2

---

## Troubleshooting

### "Cannot connect to database"
- Check `config.php` database credentials
- Verify MySQL is running
- Check database name exists

### "Geolocation not working"
- Check browser has permission (Chrome → location icon)
- Only works on HTTPS in production
- Test on localhost works without HTTPS
- Device must have GPS/WiFi location services enabled

### Email not sending
- Check `SENDER_EMAIL` in config.php
- Verify PHP mail() is configured
- Check server error logs: `/var/log/apache2/error.log`
- Test with: `php -m | grep mail`

### Login fails with right credentials
- Check password is correct (case-sensitive)
- Clear browser cache / cookies
- Try different browser
- Check user account exists in database

### Attendance marking fails
- Student outside radius → Move closer to GPS coordinates
- Session not active → Lecturer must create new session
- Browser denied location → Grant permission
- Device locked to different student → End session first

---

## File Structure

```
campus_track/
├── index.php                    (Landing page)
├── dashboard_student.php        (Student dashboard)
├── dashboard_lecturer.php       (Lecturer dashboard)
├── config.php                   (Database config)
├── Auth.php                     (Auth class)
├── Attendance.php               (Attendance class)
├── LecturerManager.php          (Lecturer class)
├── EmailNotifier.php            (Email class)
├── api_auth.php                 (Auth endpoints)
├── api_attendance.php           (Attendance endpoints)
├── api_lecturer.php             (Lecturer endpoints)
├── database.sql                 (Database schema)
├── assets/
│   ├── css/
│   │   ├── style.css           (Main styles)
│   │   └── dashboard.css       (Dashboard styles)
│   └── js/
│       └── main.js             (Frontend logic)
├── README.md                    (Documentation)
└── SETUP.md                     (This file)
```

---

## Security Checklist for Production

- [ ] Change all default passwords
- [ ] Use HTTPS (SSL certificate)
- [ ] Set proper file permissions (644 for files, 755 for directories)
- [ ] Move config.php outside web root or use environment variables
- [ ] Enable database authentication (remove anonymous users)
- [ ] Regular database backups
- [ ] Monitor server logs
- [ ] Update PHP to latest version
- [ ] Configure email service properly
- [ ] Add rate limiting to API endpoints
- [ ] Set secure session cookie settings

---

## Performance Tips

- Limit attendance sessions kept (archive old ones)
- Add database indexes for large student lists
- Use Redis for session storage if many concurrent users
- Enable gzip compression
- Use CDN for static assets

---

## Next Steps

1. **Create multiple test courses**
2. **Test with multiple students**
3. **Test on different devices**
4. **Verify email notifications**
5. **Review security settings**
6. **Set up backups**
7. **Deploy to production with HTTPS**

---

## Support

Check the main README.md for:
- API endpoint documentation
- Architecture overview
- Security features
- Best practices

For issues:
1. Check browser console (F12) for JavaScript errors
2. Check server logs
3. Verify database connection
4. Test with sample data

---

**Last Updated:** February 2026

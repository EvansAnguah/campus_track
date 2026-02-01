# campus_track
A modern, GPS-enabled attendance tracking system designed for tertiary institutions. Lecturers can create location-bounded attendance sessions, and students can only mark attendance when physically present within a defined radius.
MASTER PROMPT

Act as a senior software engineer.

Design and build a web-based, location-based attendance system for a tertiary institution.
The system must be realistic, secure, simple, and explainable.

==================================================

IMPORTANT LIMITS (MUST FOLLOW STRICTLY)

==================================================

Do NOT assume or name any specific programming language unless explicitly asked

Use only browser-supported capabilities and basic server-side logic

Do NOT rely on paid APIs or external services

Do NOT add biometric, face recognition, camera, or physical fingerprint features

Do NOT auto-create student accounts

Do NOT invent features outside the scope defined below

If any requirement is unclear, ask before assuming

Email integration must exist for:

Forgot password

Session start / session reminder alerts

==================================================

PROJECT GOAL

==================================================

Lecturers create attendance sessions.
Students can mark attendance only when physically present within a defined GPS radius during an active session.

==================================================

ACTORS

==================================================

Student

Lecturer (Admin)

==================================================

ATTENDANCE SESSION LOGIC

==================================================

A lecturer creates an attendance session with:

Course

GPS latitude

GPS longitude

Allowed radius (meters)

Start condition

End condition

A student may mark attendance only if all conditions are met:

Session is active

Student is authenticated

Browser grants location access

Student location is within the allowed radius

==================================================

DEVICE & SESSION CONTROL

==================================================

One device may have only one active session lock at a time

A device must not log into multiple student accounts during an active session

Even if the student logs out

Even if another student tries to log in

Device restriction applies only during an active session

Device restriction is not permanent

Use non-invasive browser or platform-based identifiers to identify a device

If a device already has an active session lock:

Block all new student login attempts on that device

==================================================

DATA STRUCTURE (STRICT)

==================================================

Use ONLY the following entities:

Students

Lecturers

Courses

Attendance Sessions

Attendance Records

User Sessions (for device/session control)

Rules:

Keep schemas minimal

Avoid unnecessary fields

Clearly explain the purpose of each entity

==================================================

SECURITY REQUIREMENTS

==================================================

Never store passwords in plain text

Always validate attendance server-side

Prevent duplicate attendance records

Protect authentication and sessions from hijacking

Never blindly trust client-side data

Do NOT use demo data or static placeholder logic

==================================================

UI / VISUAL RULES

==================================================

Interface must look modern and academic

Subtle animations are allowed

3D or animated visuals may be used only as background decoration

Visual elements must never control authentication or attendance logic

==================================================

WORKFLOW REQUIREMENT

==================================================

Proceed strictly in this order:

Explain the full system flow step-by-step

Design the database schema

Build authentication and onboarding

Implement attendance logic

Add UI enhancements last

Do NOT jump ahead or skip steps.

==================================================

STUDENT FUNCTIONALITY

==================================================

Students create accounts using:

Full name

email

Index number

Password (set and confirm)

Login requires:

Index number

Password

Students can:

Mark attendance during active sessions if within radius

View personal attendance history

Students receive email alerts when:

A session begins

A session is about to begin (timer-based if scheduled)

==================================================

LECTURER (ADMIN) FUNCTIONALITY

==================================================

Lecturer can:

View all students in the system (sidebar list)

Add students manually through the system

Add courses (required for session creation)

Create and manage attendance sessions

View session history filtered by:

Days

Weeks

Months

See attendance status:

Green indicator = attended

Red indicator = absent

After one hour of session closure:

Status indicators reset (lights go off)

Generate PDF reports of attendance history

==================================================

ADDITIONAL RULE – ACTIVE SESSION DEVICE LOCKING

==================================================

When an attendance session is active:

Generate a software-based Device ID

No biometrics

No camera

No Face ID

No physical fingerprint scanning

The first student account that successfully logs in on a device during an active session:

Locks that device to the student account

Store server-side:

Device ID

Student ID

Active Session ID

Lock status = active

Enforcement Rules

Logging out does NOT remove the device lock

During the active session:

The device may log back in only to the same student account

Any attempt to log into a different student account must be blocked

Display a clear message:

“This device is currently locked to another student for an active attendance session.”

Session Boundaries

Device locking applies only while a lecturer’s session is active

When the lecturer ends the session:

Automatically release all device locks linked to that session

After release:

The device can log into any student account normally

Security Constraints

All checks must be enforced server-side

Logging out, refreshing, browser restart, or app restart must not bypass the lock

Device lock logic must not affect lecturer accounts

Integration Note

This feature must integrate with existing authentication, attendance, and session logic

Do NOT alter unrelated system behavior
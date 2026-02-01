<?php
/**
 * Campus Track - Email Notification System
 * 
 * Sends email alerts for session starts and attendance marks
 */

class EmailNotifier {
    private $senderEmail;
    private $senderName;

    public function __construct() {
        $this->senderEmail = SENDER_EMAIL;
        $this->senderName = SENDER_NAME;
    }

    /**
     * Send session start notification to students
     * 
     * @param string $studentEmail
     * @param string $studentName
     * @param string $courseCode
     * @param string $courseName
     * @param string $sessionLocation
     * @return bool
     */
    public function sendSessionStartAlert($studentEmail, $studentName, $courseCode, $courseName, $sessionLocation) {
        $subject = "Attendance Session Started: {$courseCode}";
        
        $message = "
        <html>
            <body style='font-family: Arial, sans-serif; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #2c3e50;'>Attendance Session Active</h2>
                    
                    <p>Hi {$studentName},</p>
                    
                    <p>An attendance session has started for your course:</p>
                    
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <p><strong>Course:</strong> {$courseName} ({$courseCode})</p>
                        <p><strong>Location:</strong> {$sessionLocation}</p>
                        <p><strong>Time:</strong> Now Active</p>
                    </div>
                    
                    <p>Please open the Campus Track application and mark your attendance if you are within the required location.</p>
                    
                    <p style='color: #7f8c8d; font-size: 12px; margin-top: 30px;'>
                        This is an automated email. Please do not reply.
                    </p>
                </div>
            </body>
        </html>
        ";

        return $this->send($studentEmail, $subject, $message);
    }

    /**
     * Send attendance confirmation email
     * 
     * @param string $studentEmail
     * @param string $studentName
     * @param string $courseCode
     * @param string $courseName
     * @param string $markedTime
     * @param float $distance
     * @param int $radius
     * @return bool
     */
    public function sendAttendanceConfirmation($studentEmail, $studentName, $courseCode, $courseName, $markedTime, $distance, $radius) {
        $subject = "Attendance Marked: {$courseCode}";
        $distanceFromEdge = $radius - $distance;
        
        $message = "
        <html>
            <body style='font-family: Arial, sans-serif; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #27ae60;'>✓ Attendance Confirmed</h2>
                    
                    <p>Hi {$studentName},</p>
                    
                    <p>Your attendance has been successfully marked:</p>
                    
                    <div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745;'>
                        <p><strong>Course:</strong> {$courseName} ({$courseCode})</p>
                        <p><strong>Marked At:</strong> {$markedTime}</p>
                        <p><strong>Location Status:</strong> ✓ Within Zone (" . round($distanceFromEdge, 2) . "m inside)</p>
                    </div>
                    
                    <p style='color: #7f8c8d; font-size: 12px; margin-top: 30px;'>
                        This is an automated email. Please do not reply.
                    </p>
                </div>
            </body>
        </html>
        ";

        return $this->send($studentEmail, $subject, $message);
    }

    /**
     * Send forgot password reset link
     * 
     * @param string $email
     * @param string $name
     * @param string $resetLink
     * @return bool
     */
    public function sendPasswordReset($email, $name, $resetLink) {
        $subject = "Reset Your Password - Campus Track";
        
        $message = "
        <html>
            <body style='font-family: Arial, sans-serif; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #2c3e50;'>Password Reset Request</h2>
                    
                    <p>Hi {$name},</p>
                    
                    <p>We received a request to reset your password. Click the link below to set a new password:</p>
                    
                    <p style='margin: 30px 0;'>
                        <a href='{$resetLink}' style='display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px;'>Reset Password</a>
                    </p>
                    
                    <p style='color: #e74c3c;'><strong>This link will expire in 1 hour.</strong></p>
                    
                    <p>If you didn't request this, please ignore this email.</p>
                    
                    <p style='color: #7f8c8d; font-size: 12px; margin-top: 30px;'>
                        This is an automated email. Please do not reply.
                    </p>
                </div>
            </body>
        </html>
        ";

        return $this->send($email, $subject, $message);
    }

    /**
     * Send session reminder (before session starts)
     * 
     * @param string $studentEmail
     * @param string $studentName
     * @param string $courseCode
     * @param string $courseName
     * @param string $sessionStartTime
     * @param int $minutesUntilStart
     * @return bool
     */
    public function sendSessionReminder($studentEmail, $studentName, $courseCode, $courseName, $sessionStartTime, $minutesUntilStart) {
        $subject = "Upcoming: {$courseCode} Attendance Session";
        
        $message = "
        <html>
            <body style='font-family: Arial, sans-serif; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #f39c12;'>⏰ Attendance Session Reminder</h2>
                    
                    <p>Hi {$studentName},</p>
                    
                    <p>An attendance session for your course will start soon:</p>
                    
                    <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                        <p><strong>Course:</strong> {$courseName} ({$courseCode})</p>
                        <p><strong>Starts In:</strong> {$minutesUntilStart} minute(s)</p>
                        <p><strong>Time:</strong> {$sessionStartTime}</p>
                    </div>
                    
                    <p>Make sure you are at the required location when the session starts to mark your attendance.</p>
                    
                    <p style='color: #7f8c8d; font-size: 12px; margin-top: 30px;'>
                        This is an automated email. Please do not reply.
                    </p>
                </div>
            </body>
        </html>
        ";

        return $this->send($studentEmail, $subject, $message);
    }

    /**
     * Generic send email function
     * Uses PHP mail() function - can be replaced with SMTP library
     * 
     * @param string $to
     * @param string $subject
     * @param string $message
     * @return bool
     */
    private function send($to, $subject, $message) {
        // Email headers
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->senderName} <{$this->senderEmail}>\r\n";
        $headers .= "Reply-To: {$this->senderEmail}\r\n";

        // Send email
        return mail($to, $subject, $message, $headers);
    }
}

?>

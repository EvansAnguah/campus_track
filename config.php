<?php
/**
 * Campus Track - Configuration & Database Connection
 * 
 * Provides centralized database connection and configuration
 */

// Error reporting (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ============================================
// CONFIGURATION
// ============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'campus_track');
define('SESSION_TIMEOUT', 3600); // 1 hour
define('TOKEN_LENGTH', 32);
define('BCRYPT_COST', 10);

// Email configuration (if using PHP mail)
define('SENDER_EMAIL', 'noreply@campustrack.edu');
define('SENDER_NAME', 'Campus Track');

// ============================================
// DATABASE CONNECTION
// ============================================
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->connection->connect_error) {
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed'
            ]));
        }
        
        $this->connection->set_charset('utf8mb4');
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function prepare($query) {
        return $this->connection->prepare($query);
    }

    public function query($query) {
        return $this->connection->query($query);
    }

    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }

    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// ============================================
// RESPONSE HELPERS
// ============================================
function sendJSON($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

function sendSuccess($message, $data = []) {
    sendJSON(array_merge(['success' => true, 'message' => $message], $data));
}

function sendError($message, $statusCode = 400) {
    sendJSON(['success' => false, 'message' => $message], $statusCode);
}

// ============================================
// SESSION & AUTHENTICATION HELPERS
// ============================================
function generateToken() {
    return bin2hex(random_bytes(TOKEN_LENGTH / 2));
}

function generateDeviceId() {
    // Generate a browser-based device fingerprint
    // This combines multiple browser properties for identification
    $components = [
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'unknown',
        $_SERVER['HTTP_ACCEPT_ENCODING'] ?? 'unknown'
    ];
    
    // Create a consistent device ID from browser properties
    return hash('sha256', implode('|', $components));
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// ============================================
// DISTANCE CALCULATION (Haversine formula)
// ============================================
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // meters
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

// ============================================
// CLEANUP OLD SESSIONS
// ============================================
function cleanupExpiredSessions() {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM user_sessions WHERE expires_at < NOW()");
    $stmt->execute();
    $stmt->close();
}

// Run cleanup on every request (optional - can be moved to cron)
cleanupExpiredSessions();
?>

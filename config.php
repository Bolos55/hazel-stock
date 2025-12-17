<?php
/**
 * Configuration File - Restaurant Stock System
 * แก้ไขแล้ว: เพิ่ม error handling, validation, และ security
 */

// Error Reporting (ปิดใน production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // เปลี่ยนเป็น 0 ใน production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php-errors.log');

// Timezone
date_default_timezone_set('Asia/Bangkok');

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_NAME', getenv('DB_NAME') ?: 'restaurant_stock');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('PHOTOS_DIR', __DIR__ . '/stock-photos');
define('EXCEL_DIR', __DIR__ . '/excel-exports');
define('LOGS_DIR', __DIR__ . '/logs');
define('MAX_PHOTO_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_PHOTO_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']);
define('ALLOWED_PHOTO_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Create necessary directories
$dirs = [PHOTOS_DIR, EXCEL_DIR, LOGS_DIR];
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        @mkdir($dir, 0755, true);
    }
}

/**
 * Database Connection Class
 */
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            // เพิ่ม SSL options ถ้ามี
            if (getenv('DB_SSL_CA')) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = getenv('DB_SSL_CA');
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // ป้องกัน clone
    private function __clone() {}
    
    // ป้องกัน unserialize
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * JSON Response Helper
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Error Response Helper
 */
function errorResponse($message, $statusCode = 400, $details = null) {
    $response = [
        'success' => false,
        'message' => $message
    ];
    
    if ($details !== null && !in_array(ini_get('display_errors'), ['0', 'Off', ''])) {
        $response['details'] = $details;
    }
    
    error_log("Error Response ($statusCode): $message" . ($details ? " | Details: " . json_encode($details) : ""));
    jsonResponse($response, $statusCode);
}

/**
 * Get Current Date (Thailand)
 */
function getCurrentDate() {
    return date('Y-m-d');
}

/**
 * Get Today's Photo Directory
 */
function getTodayPhotoDir() {
    $dir = PHOTOS_DIR . '/' . getCurrentDate();
    if (!file_exists($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir;
}

/**
 * Validate Employee Name
 */
function validateEmployeeName($name) {
    $name = trim($name);
    if (empty($name)) {
        return ['valid' => false, 'message' => 'กรุณากรอกชื่อพนักงาน'];
    }
    if (mb_strlen($name) < 2) {
        return ['valid' => false, 'message' => 'ชื่อพนักงานต้องมีอย่างน้อย 2 ตัวอักษร'];
    }
    if (mb_strlen($name) > 100) {
        return ['valid' => false, 'message' => 'ชื่อพนักงานยาวเกินไป'];
    }
    return ['valid' => true, 'name' => $name];
}

/**
 * Validate Photo File
 */
function validatePhotoFile($file) {
    // ตรวจสอบว่ามีไฟล์
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['valid' => false, 'message' => 'ไม่พบไฟล์ที่อัปโหลด'];
    }
    
    // ตรวจสอบ upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'ไฟล์มีขนาดใหญ่เกินที่กำหนด',
            UPLOAD_ERR_FORM_SIZE => 'ไฟล์มีขนาดใหญ่เกินไป',
            UPLOAD_ERR_PARTIAL => 'อัปโหลดไฟล์ไม่สมบูรณ์',
            UPLOAD_ERR_NO_TMP_DIR => 'ไม่พบโฟลเดอร์ชั่วคราว',
            UPLOAD_ERR_CANT_WRITE => 'ไม่สามารถเขียนไฟล์ได้',
        ];
        return ['valid' => false, 'message' => $errors[$file['error']] ?? 'เกิดข้อผิดพลาดในการอัปโหลด'];
    }
    
    // ตรวจสอบขนาดไฟล์
    if ($file['size'] > MAX_PHOTO_SIZE) {
        return ['valid' => false, 'message' => 'ไฟล์มีขนาดใหญ่เกิน ' . (MAX_PHOTO_SIZE / 1024 / 1024) . ' MB'];
    }
    
    // ตรวจสอบ MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_PHOTO_TYPES)) {
        return ['valid' => false, 'message' => 'ประเภทไฟล์ไม่ถูกต้อง (อนุญาตเฉพาะ JPG, PNG, WEBP)'];
    }
    
    // ตรวจสอบ extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_PHOTO_EXTENSIONS)) {
        return ['valid' => false, 'message' => 'นามสกุลไฟล์ไม่ถูกต้อง'];
    }
    
    // ตรวจสอบว่าเป็นรูปภาพจริง
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['valid' => false, 'message' => 'ไฟล์ไม่ใช่รูปภาพที่ถูกต้อง'];
    }
    
    return [
        'valid' => true,
        'mime_type' => $mimeType,
        'extension' => $ext,
        'size' => $file['size']
    ];
}

/**
 * Sanitize Filename
 */
function sanitizeFilename($filename) {
    // ลบ path traversal
    $filename = basename($filename);
    // ลบอักขระพิเศษ
    $filename = preg_replace('/[^a-zA-Z0-9ก-๙._-]/', '-', $filename);
    // ลบ dot ซ้ำ
    $filename = preg_replace('/\.+/', '.', $filename);
    return $filename;
}

/**
 * Log Activity
 */
function logActivity($message, $data = []) {
    $logFile = LOGS_DIR . '/activity-' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $dataStr = !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : '';
    $logMessage = "[$timestamp] $message" . ($dataStr ? " | Data: $dataStr" : "") . "\n";
    @file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Check if Today's Record Exists
 */
function hasTodayRecord() {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM daily_stock_records WHERE record_date = ?");
        $stmt->execute([getCurrentDate()]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    } catch (Exception $e) {
        error_log("Error checking today's record: " . $e->getMessage());
        return false;
    }
}

// Security Headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
?>

<?php
// config.php - Database Configuration and Global Settings

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'hazel_stock');
define('DB_USER', 'root'); // Change in production
define('DB_PASS', ''); // Change in production
define('DB_CHARSET', 'utf8mb4');

// Timezone Configuration
date_default_timezone_set('Asia/Bangkok');

// Application Settings
define('PHOTOS_DIR', __DIR__ . '/stock-photos');
define('EXCEL_DIR', __DIR__ . '/excel-exports');
define('MAX_PHOTO_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_PHOTO_TYPES', ['image/jpeg', 'image/jpg', 'image/png']);

// Create necessary directories
if (!file_exists(PHOTOS_DIR)) {
    mkdir(PHOTOS_DIR, 0755, true);
}
if (!file_exists(EXCEL_DIR)) {
    mkdir(EXCEL_DIR, 0755, true);
}

// Database Connection Class
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
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
}

// Utility Functions
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getCurrentDate() {
    return date('Y-m-d');
}

function getTodayPhotoDir() {
    $dir = PHOTOS_DIR . '/' . getCurrentDate();
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}
?>
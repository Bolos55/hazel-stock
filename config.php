<?php
// ================================
// config.php (RENDER + AIVEN SAFE)
// ================================

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load environment variables from .env file
require_once __DIR__ . '/load-env.php';

date_default_timezone_set('Asia/Bangkok');

// Set timezone for database connections too
ini_set('date.timezone', 'Asia/Bangkok');

/* ================= DATABASE ENV ================= */
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_NAME', getenv('DB_NAME') ?: 'hazel_stock');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

/* ================= APP SETTINGS ================= */
define('PHOTOS_DIR', __DIR__ . '/stock-photos');
define('EXCEL_DIR', __DIR__ . '/excel-exports');
define('MAX_PHOTO_SIZE', 2 * 1024 * 1024); // Reduced to 2MB for speed
define('ALLOWED_PHOTO_TYPES', ['image/jpeg', 'image/png']);

/* ================= CREATE DIR (SAFE) ================= */
@mkdir(PHOTOS_DIR, 0755, true);
@mkdir(EXCEL_DIR, 0755, true);
/* ================= DATABASE CLASS ================= */
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        // Only try to connect if we have proper environment variables
        if (!getenv('DB_HOST') || !getenv('DB_NAME') || !getenv('DB_USER')) {
            throw new Exception('Database environment variables not properly set');
        }
        
        try {
            // Check if using PostgreSQL or MySQL
            $isPostgreSQL = (DB_PORT == 5432 || strpos(DB_HOST, 'postgres') !== false);
            
            // Try PostgreSQL first, fallback to MySQL if driver not available
            if ($isPostgreSQL && extension_loaded('pdo_pgsql')) {
                $dsn = sprintf(
                    "pgsql:host=%s;port=%s;dbname=%s",
                    DB_HOST,
                    DB_PORT,
                    DB_NAME
                );
            } elseif (extension_loaded('pdo_mysql')) {
                // Fallback to MySQL syntax
                $dsn = sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                    DB_HOST,
                    DB_PORT,
                    DB_NAME,
                    DB_CHARSET
                );
            } else {
                throw new Exception('Neither PostgreSQL nor MySQL PDO drivers are available. Available extensions: ' . implode(', ', get_loaded_extensions()));
            }

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 10, // 10 second timeout
                PDO::ATTR_PERSISTENT => false // Disable persistent connections
            ];
            
            // Add MySQL-specific options only for MySQL
            if (!$isPostgreSQL || !extension_loaded('pdo_pgsql')) {
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
                $options[PDO::MYSQL_ATTR_CONNECT_TIMEOUT] = 10; // MySQL specific timeout
            }

            // Retry connection up to 3 times
            $maxRetries = 3;
            $retryCount = 0;
            $lastException = null;
            
            while ($retryCount < $maxRetries) {
                try {
                    $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
                    break; // Success, exit retry loop
                } catch (PDOException $e) {
                    $lastException = $e;
                    $retryCount++;
                    
                    if ($retryCount < $maxRetries) {
                        // Wait 1 second before retry
                        sleep(1);
                    }
                }
            }
            
            // If all retries failed, throw the last exception
            if ($retryCount >= $maxRetries && $lastException) {
                throw $lastException;
            }

        } catch (PDOException $e) {
            throw new Exception('DB Connection failed: ' . $e->getMessage());
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

/* ================= HELPERS ================= */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getCurrentDate() {
    return date('Y-m-d');
}

function getTodayPhotoDir() {
    $dir = PHOTOS_DIR . '/' . getCurrentDate();
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            throw new Exception('Cannot create photo directory: ' . $dir);
        }
    }
    return $dir;
}

function validateDate($date) {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && 
           DateTime::createFromFormat('Y-m-d', $date) !== false;
}

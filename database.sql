<?php
// ================================
// config.php (PRODUCTION READY)
// ================================

date_default_timezone_set('Asia/Bangkok');

/* ================= DATABASE ENV ================= */
define('DB_HOST', $_ENV['DB_HOST'] ?? getenv('DB_HOST'));
define('DB_PORT', $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? 3306);
define('DB_NAME', $_ENV['DB_NAME'] ?? getenv('DB_NAME'));
define('DB_USER', $_ENV['DB_USER'] ?? getenv('DB_USER'));
define('DB_PASS', $_ENV['DB_PASS'] ?? getenv('DB_PASS'));
define('DB_CHARSET', 'utf8mb4');

if (!DB_HOST || !DB_NAME || !DB_USER) {
    die('❌ Database environment variables not set');
}

/* ================= APP SETTINGS ================= */
define('PHOTOS_DIR', __DIR__ . '/stock-photos');
define('EXCEL_DIR', __DIR__ . '/excel-exports');
define('MAX_PHOTO_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_PHOTO_TYPES', ['image/jpeg', 'image/png']);

/* ================= CREATE DIR (SAFE) ================= */
@mkdir(PHOTOS_DIR, 0755, true);
@mkdir(EXCEL_DIR, 0755, true);

/* ================= DATABASE CLASS ================= */
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST .
                   ";port=" . DB_PORT .
                   ";dbname=" . DB_NAME .
                   ";charset=" . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,

                // ⭐ สำคัญสำหรับ Aiven (SSL)
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];

            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("❌ DB Connection failed: " . $e->getMessage());
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
    @mkdir($dir, 0755, true);
    return $dir;
}
<?php
// ================================
// config.php (RENDER + AIVEN SAFE)
// ================================

date_default_timezone_set('Asia/Bangkok');

/* ================= DATABASE ENV ================= */
define('DB_HOST', getenv('DB_HOST'));
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_CHARSET', 'utf8mb4');

/* ====== CHECK ENV (สำคัญมาก) ====== */
if (!DB_HOST || !DB_NAME || !DB_USER) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Database environment variables not set',
        'debug' => [
            'DB_HOST' => DB_HOST,
            'DB_NAME' => DB_NAME,
            'DB_USER' => DB_USER,
            'DB_PORT' => DB_PORT
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ================= DATABASE CLASS ================= */
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,

                // สำคัญสำหรับ Aiven
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];

            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'DB Connection failed',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
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

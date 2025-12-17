<?php
// ================================
// config.php (RENDER + AIVEN FINAL)
// ================================

date_default_timezone_set('Asia/Bangkok');
header('Content-Type: application/json; charset=utf-8');

/* ================= DATABASE ENV ================= */
define('DB_HOST', getenv('DB_HOST'));
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_SSL', getenv('DB_SSL') ?: 'true');
define('DB_CHARSET', 'utf8mb4');

if (!DB_HOST || !DB_NAME || !DB_USER) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database ENV not set',
        'env' => [
            'DB_HOST' => DB_HOST,
            'DB_NAME' => DB_NAME,
            'DB_USER' => DB_USER,
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ================= DATABASE CLASS ================= */
class Database {
    private static $instance = null;
    private PDO $conn;

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
            ];

            // 🔐 Aiven SSL
            if (DB_SSL === 'true') {
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }

            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'DB Connection failed',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->conn;
    }
}

/* ================= HELPERS ================= */
function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

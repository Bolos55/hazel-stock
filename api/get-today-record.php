<?php
// Set content type first
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../work-date-helper.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Config file error: ' . $e->getMessage()
    ]);
    exit;
}

try {
    // Try to get database connection
    $db = Database::getInstance()->getConnection();

    // Use work date (resets at 3 AM instead of midnight)
    $today = getWorkDate();

    $stmt = $db->prepare("
        SELECT COUNT(*) AS total
        FROM daily_stock_records
        WHERE record_date = :today
    ");
    $stmt->execute(['today' => $today]);

    $result = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'date' => $today,
        'has_records' => $result['total'] > 0,
        'total_records' => (int)$result['total']
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Handle database connection errors gracefully
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error',
        'error' => $e->getMessage(),
        'debug' => [
            'file' => __FILE__,
            'line' => __LINE__,
            'db_host' => defined('DB_HOST') ? DB_HOST : 'NOT DEFINED',
            'db_name' => defined('DB_NAME') ? DB_NAME : 'NOT DEFINED'
        ]
    ], JSON_UNESCAPED_UNICODE);
}

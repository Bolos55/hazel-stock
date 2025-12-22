<?php
require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Try to get database connection
    $db = Database::getInstance()->getConnection();

    $today = date('Y-m-d');

    $stmt = $db->prepare("
        SELECT COUNT(*) AS total
        FROM daily_stock_records
        WHERE record_date = :today
    ");
    $stmt->execute(['today' => $today]);

    $result = $stmt->fetch();

    jsonResponse([
        'success' => true,
        'date' => $today,
        'has_records' => $result['total'] > 0,
        'total_records' => (int)$result['total']
    ]);

} catch (Exception $e) {
    // Handle database connection errors gracefully
    jsonResponse([
        'success' => false,
        'message' => 'Database connection error',
        'error' => $e->getMessage(),
        'debug' => [
            'file' => __FILE__,
            'line' => __LINE__,
            'db_host' => DB_HOST,
            'db_name' => DB_NAME
        ]
    ], 500);
}

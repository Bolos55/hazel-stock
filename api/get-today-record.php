<?php
require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

try {
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

} catch (Throwable $e) {
    jsonResponse([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => __FILE__,
            'line' => __LINE__
        ]
    ], 500);
}

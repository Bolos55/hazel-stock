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
        'message' => $e->getMessage()
    ], 500);
}
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'exists' => ($row['total'] > 0),
        'date' => $today
    ]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

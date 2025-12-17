<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    $today = date('Y-m-d');

    $stmt = $db->prepare("
        SELECT COUNT(*) AS total
        FROM daily_stock_records
        WHERE record_date = :today
    ");
    $stmt->execute(['today' => $today]);
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

<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = Database::getInstance()->getConnection();

    $today = date('Y-m-d');

    $stmt = $db->prepare("
        SELECT employee_id, created_at
        FROM daily_stock_records
        WHERE record_date = :today
        LIMIT 1
    ");
    $stmt->execute(['today' => $today]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'exists'  => $record ? true : false,
        'record'  => $record ? [
            'employee_name' => 'มีการบันทึกแล้ว',
            'submitted_at'  => $record['created_at']
        ] : null,
        'date' => $today
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

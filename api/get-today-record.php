<?php
require_once __DIR__ . '/../config.php';

try {
    $db = Database::getInstance()->getConnection();
    $today = date('Y-m-d');

    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM daily_stock_records 
        WHERE record_date = :today
    ");
    $stmt->execute(['today' => $today]);

    jsonResponse([
        'success' => true,
        'exists' => $stmt->fetchColumn() > 0
    ]);
} catch (Throwable $e) {
    jsonResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 500);
}

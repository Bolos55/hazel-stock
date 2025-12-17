<?php
require_once '../config.php';

$db = Database::getInstance()->getConnection();
$today = date('Y-m-d');

$stmt = $db->prepare("
    SELECT employee_name, submitted_at
    FROM daily_stock_records
    WHERE record_date = :d
    LIMIT 1
");
$stmt->execute(['d' => $today]);

$row = $stmt->fetch();

jsonResponse([
    'success' => true,
    'exists' => !!$row,
    'record' => $row
]);

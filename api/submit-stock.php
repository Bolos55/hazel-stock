<?php
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);
$db = Database::getInstance()->getConnection();
$today = date('Y-m-d');

$db->beginTransaction();

foreach ($data['stock_data'] as $item) {
    $stmt = $db->prepare("
        INSERT INTO daily_stock_records
        (record_date, material_id, remaining_quantity, unit, photo_path, employee_name)
        VALUES (:d, :mid, :q, :u, :p, :e)
    ");

    $stmt->execute([
        'd' => $today,
        'mid' => $item['material_id'],
        'q' => $item['quantity'],
        'u' => $item['unit'],
        'p' => $item['photo'],
        'e' => $data['employee_name']
    ]);
}

$db->commit();

jsonResponse(['success' => true]);

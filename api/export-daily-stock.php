<?php
require_once '../config.php';
require_once '../work-date-helper.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = Database::getInstance()->getConnection();

    $recordDate = $_GET['date'] ?? getWorkDate();

    // เพิ่ม date validation เพื่อป้องกัน SQL injection
    if (!validateDate($recordDate)) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD');
    }

    $sql = "
        SELECT
            r.material_name,
            r.unit,
            d.remaining_quantity,
            d.record_date,
            e.full_name as employee_name
        FROM daily_stock_records d
        JOIN raw_materials r ON d.material_id = r.id
        LEFT JOIN employees e ON d.employee_id = e.id
        WHERE d.record_date = :record_date
        ORDER BY r.display_order ASC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':record_date' => $recordDate
    ]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse([
        'success' => true,
        'date' => $recordDate,
        'data' => $data,
        'count' => count($data)
    ]);

} catch (Throwable $e) {
    jsonResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 500);
}

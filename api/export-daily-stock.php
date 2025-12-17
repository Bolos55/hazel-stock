<?php
require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = Database::getInstance()->getConnection();

    $recordDate = $_GET['date'] ?? date('Y-m-d');

    $sql = "
        SELECT
            r.material_name,
            r.unit,
            d.remaining_quantity,
            d.record_date,
            e.employee_name
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

    echo json_encode([
        'success' => true,
        'date' => $recordDate,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

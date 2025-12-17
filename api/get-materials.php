<?php
require_once __DIR__ . '/../config.php';

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->query("
        SELECT id, material_name, unit
        FROM raw_materials
        ORDER BY material_name
    ");

    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse([
        'success' => true,
        'materials' => $materials
    ]);

} catch (Throwable $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Database error'
    ], 500);
}

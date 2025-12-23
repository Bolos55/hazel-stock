<?php
require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Try to get database connection
    $db = Database::getInstance()->getConnection();

    // Try to get materials with sub_unit first
    try {
        $stmt = $db->query("
            SELECT 
                id, 
                material_name, 
                unit,
                COALESCE(sub_unit, '') as sub_unit
            FROM raw_materials
            ORDER BY display_order ASC, material_name ASC
        ");
    } catch (Exception $e) {
        // If sub_unit column doesn't exist, fall back to basic query
        $stmt = $db->query("
            SELECT 
                id, 
                material_name, 
                unit,
                '' as sub_unit
            FROM raw_materials
            ORDER BY display_order ASC, material_name ASC
        ");
    }

    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse([
        'success' => true,
        'materials' => $materials,
        'count' => count($materials)
    ]);

} catch (Exception $e) {
    // Handle database connection errors gracefully
    jsonResponse([
        'success' => false,
        'message' => 'Database connection error',
        'error' => $e->getMessage(),
        'debug' => [
            'file' => __FILE__,
            'db_host' => DB_HOST,
            'db_name' => DB_NAME
        ]
    ], 500);
}

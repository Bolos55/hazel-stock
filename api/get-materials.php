<?php
// Enable error display for debugging on production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    // Handle database connection errors gracefully with detailed debugging
    http_response_code(500);
    jsonResponse([
        'success' => false,
        'message' => 'Database connection error',
        'error' => $e->getMessage(),
        'debug' => [
            'file' => __FILE__,
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'db_host' => DB_HOST,
            'db_name' => DB_NAME,
            'db_user' => DB_USER,
            'db_port' => DB_PORT,
            'env_check' => [
                'DB_HOST' => getenv('DB_HOST') ? 'SET' : 'NOT SET',
                'DB_NAME' => getenv('DB_NAME') ? 'SET' : 'NOT SET',
                'DB_USER' => getenv('DB_USER') ? 'SET' : 'NOT SET',
                'DB_PASS' => getenv('DB_PASS') ? 'SET' : 'NOT SET',
                'DB_PORT' => getenv('DB_PORT') ? 'SET' : 'NOT SET'
            ]
        ]
    ], 500);
}

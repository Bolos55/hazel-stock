<?php
// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set content type first
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../config.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Config file error: ' . $e->getMessage()
    ]);
    exit;
}

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

    echo json_encode([
        'success' => true,
        'materials' => $materials,
        'count' => count($materials)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Handle database connection errors gracefully with detailed debugging
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error',
        'error' => $e->getMessage(),
        'debug' => [
            'file' => __FILE__,
            'line' => $e->getLine(),
            'db_host' => defined('DB_HOST') ? DB_HOST : 'NOT DEFINED',
            'db_name' => defined('DB_NAME') ? DB_NAME : 'NOT DEFINED',
            'db_user' => defined('DB_USER') ? DB_USER : 'NOT DEFINED',
            'db_port' => defined('DB_PORT') ? DB_PORT : 'NOT DEFINED',
            'env_check' => [
                'DB_HOST' => getenv('DB_HOST') ? 'SET' : 'NOT SET',
                'DB_NAME' => getenv('DB_NAME') ? 'SET' : 'NOT SET',
                'DB_USER' => getenv('DB_USER') ? 'SET' : 'NOT SET',
                'DB_PASS' => getenv('DB_PASS') ? 'SET' : 'NOT SET',
                'DB_PORT' => getenv('DB_PORT') ? 'SET' : 'NOT SET'
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);
}

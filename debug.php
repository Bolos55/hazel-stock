<?php
// Debug file to test database connection and environment
header('Content-Type: application/json; charset=utf-8');

try {
    // Test 1: Check if config.php loads
    require_once 'config.php';
    
    $debug = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION,
        'environment' => [
            'DB_HOST' => DB_HOST,
            'DB_PORT' => DB_PORT,
            'DB_NAME' => DB_NAME,
            'DB_USER' => DB_USER,
            'DB_PASS' => DB_PASS ? 'SET' : 'NOT SET'
        ],
        'constants' => [
            'PHOTOS_DIR' => defined('PHOTOS_DIR') ? PHOTOS_DIR : 'NOT DEFINED',
            'EXCEL_DIR' => defined('EXCEL_DIR') ? EXCEL_DIR : 'NOT DEFINED'
        ]
    ];
    
    // Test 2: Try database connection
    try {
        $db = Database::getInstance()->getConnection();
        $debug['database'] = [
            'connection' => 'SUCCESS',
            'server_info' => $db->getAttribute(PDO::ATTR_SERVER_VERSION)
        ];
        
        // Test 3: Check if tables exist
        $tables = ['employees', 'raw_materials', 'daily_stock_records', 'excel_export_log'];
        $debug['tables'] = [];
        
        foreach ($tables as $table) {
            try {
                $stmt = $db->query("SELECT COUNT(*) FROM {$table}");
                $count = $stmt->fetchColumn();
                $debug['tables'][$table] = "EXISTS ({$count} records)";
            } catch (Exception $e) {
                $debug['tables'][$table] = "ERROR: " . $e->getMessage();
            }
        }
        
    } catch (Exception $e) {
        $debug['database'] = [
            'connection' => 'FAILED',
            'error' => $e->getMessage()
        ];
    }
    
    echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}
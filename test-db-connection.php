<?php
/**
 * Test Database Connection
 * ทดสอบการเชื่อมต่อฐานข้อมูล
 */

require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    echo "🔍 Testing Database Connection...\n\n";
    
    // Show environment variables (without password)
    echo "📋 Environment Variables:\n";
    echo "DB_HOST: " . (getenv('DB_HOST') ?: 'NOT SET') . "\n";
    echo "DB_PORT: " . (getenv('DB_PORT') ?: 'NOT SET') . "\n";
    echo "DB_NAME: " . (getenv('DB_NAME') ?: 'NOT SET') . "\n";
    echo "DB_USER: " . (getenv('DB_USER') ?: 'NOT SET') . "\n";
    echo "DB_PASS: " . (getenv('DB_PASS') ? '***SET***' : 'NOT SET') . "\n\n";
    
    // Test database connection
    echo "🔌 Attempting to connect...\n";
    $db = Database::getInstance()->getConnection();
    
    if ($db) {
        echo "✅ Database connection successful!\n\n";
        
        // Test query
        echo "📊 Testing query...\n";
        $stmt = $db->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        if ($result) {
            echo "✅ Query test successful!\n";
            echo "Result: " . json_encode($result) . "\n\n";
        }
        
        // Check if tables exist
        echo "📋 Checking tables...\n";
        $tables = ['employees', 'raw_materials', 'daily_stock_records'];
        
        foreach ($tables as $table) {
            try {
                $stmt = $db->query("SELECT COUNT(*) as count FROM {$table}");
                $result = $stmt->fetch();
                echo "✅ Table '{$table}': {$result['count']} records\n";
            } catch (Exception $e) {
                echo "❌ Table '{$table}': " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n🎉 Database is working properly!\n";
        
    } else {
        echo "❌ Database connection failed!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
}
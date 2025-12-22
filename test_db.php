<?php
require_once 'config.php';

echo "<h2>🔍 Database Connection Test</h2>";

try {
    $db = Database::getInstance()->getConnection();
    echo "<p>✅ PDO เชื่อมต่อฐานข้อมูลสำเร็จ</p>";
    
    // Test basic queries
    echo "<h3>📊 Database Tables Test</h3>";
    
    // Test employees table
    $stmt = $db->query("SELECT COUNT(*) as count FROM employees");
    $result = $stmt->fetch();
    echo "<p>👥 Employees: {$result['count']} records</p>";
    
    // Test raw_materials table
    $stmt = $db->query("SELECT COUNT(*) as count FROM raw_materials");
    $result = $stmt->fetch();
    echo "<p>📦 Raw Materials: {$result['count']} records</p>";
    
    // Test daily_stock_records table
    $stmt = $db->query("SELECT COUNT(*) as count FROM daily_stock_records");
    $result = $stmt->fetch();
    echo "<p>📋 Daily Stock Records: {$result['count']} records</p>";
    
    // Test excel_export_log table
    $stmt = $db->query("SELECT COUNT(*) as count FROM excel_export_log");
    $result = $stmt->fetch();
    echo "<p>📄 Excel Export Logs: {$result['count']} records</p>";
    
    echo "<h3>🎯 Sample Data</h3>";
    
    // Show sample materials
    $stmt = $db->query("SELECT material_name, unit FROM raw_materials LIMIT 5");
    $materials = $stmt->fetchAll();
    echo "<p><strong>Sample Materials:</strong></p><ul>";
    foreach ($materials as $material) {
        echo "<li>{$material['material_name']} ({$material['unit']})</li>";
    }
    echo "</ul>";
    
    // Show sample employees
    $stmt = $db->query("SELECT full_name FROM employees LIMIT 3");
    $employees = $stmt->fetchAll();
    echo "<p><strong>Sample Employees:</strong></p><ul>";
    foreach ($employees as $employee) {
        echo "<li>{$employee['full_name']}</li>";
    }
    echo "</ul>";
    
    echo "<p>🎉 <strong>All tests passed! Database is ready to use.</strong></p>";
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>💡 <strong>Possible solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Check if environment variables are set correctly</li>";
    echo "<li>Verify database connection details in .env or hosting platform</li>";
    echo "<li>Make sure database schema is imported (run database.sql)</li>";
    echo "<li>Check if database server is accessible</li>";
    echo "</ul>";
}

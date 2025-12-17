<?php
require_once 'config.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "✅ PDO เชื่อมต่อฐานข้อมูล hazel_stock สำเร็จ";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}

<?php
/**
 * Urgent Fix - แก้ไขปัญหาเร่งด่วน
 */
    
// Enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🚨 แก้ไขปัญหาเร่งด่วน</h1>";

// Test 1: Check if config.php exists and works
echo "<h2>1. ตรวจสอบ config.php</h2>";
if (file_exists('config.php')) {
    echo "✅ ไฟล์ config.php มีอยู่<br>";
    try {
        require_once 'config.php';
        echo "✅ โหลด config.php สำเร็จ<br>";
        
        // Test database connection
        $db = Database::getInstance()->getConnection();
        echo "✅ เชื่อมต่อฐานข้อมูลสำเร็จ<br>";
        
        // Test simple query
        $stmt = $db->query("SELECT 1 as test");
        $result = $stmt->fetch();
        echo "✅ ทดสอบ query สำเร็จ: " . json_encode($result) . "<br>";
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
        
        // Show environment variables
        echo "<h3>Environment Variables:</h3>";
        echo "DB_HOST: " . (getenv('DB_HOST') ?: 'NOT SET') . "<br>";
        echo "DB_PORT: " . (getenv('DB_PORT') ?: 'NOT SET') . "<br>";
        echo "DB_NAME: " . (getenv('DB_NAME') ?: 'NOT SET') . "<br>";
        echo "DB_USER: " . (getenv('DB_USER') ?: 'NOT SET') . "<br>";
        echo "DB_PASS: " . (getenv('DB_PASS') ? 'SET' : 'NOT SET') . "<br>";
    }
} else {
    echo "❌ ไม่พบไฟล์ config.php<br>";
}

// Test 2: Check API files
echo "<h2>2. ตรวจสอบไฟล์ API</h2>";
$apiFiles = [
    'api/get-materials.php',
    'api/get-today-record.php',
    'api/submit-stock.php'
];

foreach ($apiFiles as $file) {
    if (file_exists($file)) {
        echo "✅ {$file} มีอยู่<br>";
    } else {
        echo "❌ ไม่พบ {$file}<br>";
    }
}

// Test 3: Check directories
echo "<h2>3. ตรวจสอบโฟลเดอร์</h2>";
$dirs = ['stock-photos', 'excel-exports'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "✅ โฟลเดอร์ {$dir} มีอยู่<br>";
        if (is_writable($dir)) {
            echo "✅ โฟลเดอร์ {$dir} เขียนได้<br>";
        } else {
            echo "⚠️ โฟลเดอร์ {$dir} เขียนไม่ได้<br>";
        }
    } else {
        echo "❌ ไม่พบโฟลเดอร์ {$dir}<br>";
        if (mkdir($dir, 0755, true)) {
            echo "✅ สร้างโฟลเดอร์ {$dir} สำเร็จ<br>";
        } else {
            echo "❌ สร้างโฟลเดอร์ {$dir} ไม่สำเร็จ<br>";
        }
    }
}

// Test 4: Test API directly
echo "<h2>4. ทดสอบ API โดยตรง</h2>";
if (isset($db)) {
    try {
        // Test get materials
        $stmt = $db->query("SELECT COUNT(*) as count FROM raw_materials");
        $result = $stmt->fetch();
        echo "✅ มีวัตถุดิบ " . $result['count'] . " รายการ<br>";
        
        // Test employees
        $stmt = $db->query("SELECT COUNT(*) as count FROM employees");
        $result = $stmt->fetch();
        echo "✅ มีพนักงาน " . $result['count'] . " คน<br>";
        
    } catch (Exception $e) {
        echo "❌ ทดสอบ API ไม่สำเร็จ: " . $e->getMessage() . "<br>";
    }
}

echo "<h2>5. แนะนำการแก้ไข</h2>";
echo "<p>หากมีปัญหา ให้ลองทำตามขั้นตอนนี้:</p>";
echo "<ol>";
echo "<li>ตรวจสอบว่า MySQL/MariaDB เปิดอยู่</li>";
echo "<li>ตรวจสอบ username/password ในไฟล์ .env</li>";
echo "<li>รัน setup.php เพื่อสร้างฐานข้อมูล</li>";
echo "<li>ตรวจสอบ permissions ของโฟลเดอร์</li>";
echo "</ol>";

echo "<p><a href='setup.php'>🛠️ ไปหน้า Setup</a> | <a href='index.php'>🏠 กลับหน้าหลัก</a></p>";
?>
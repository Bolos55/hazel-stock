<?php
/**
 * Database Connection Test Script
 * ทดสอบการเชื่อมต่อฐานข้อมูลและตรวจสอบตารางต่างๆ
 */

require_once 'config.php';

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ทดสอบการเชื่อมต่อฐานข้อมูล</title>
    <style>
        body {
            font-family: 'Sukhumvit Set', Arial, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4472C4;
            padding-bottom: 10px;
        }
        h2 {
            color: #4472C4;
            margin-top: 30px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #dc3545;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #17a2b8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #4472C4;
            color: white;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 ทดสอบระบบฐานข้อมูล</h1>
        
        <?php
        $allPassed = true;
        
        // Test 1: Database Connection
        echo "<h2>1. การเชื่อมต่อฐานข้อมูล</h2>";
        try {
            $db = Database::getInstance()->getConnection();
            echo '<div class="success">✅ เชื่อมต่อฐานข้อมูลสำเร็จ!</div>';
            echo '<div class="info">';
            echo '<strong>ข้อมูลการเชื่อมต่อ:</strong><br>';
            echo 'Host: <code>' . DB_HOST . '</code><br>';
            echo 'Port: <code>' . DB_PORT . '</code><br>';
            echo 'Database: <code>' . DB_NAME . '</code><br>';
            echo 'User: <code>' . DB_USER . '</code><br>';
            echo 'Charset: <code>' . DB_CHARSET . '</code>';
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="error">❌ ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' . $e->getMessage() . '</div>';
            $allPassed = false;
        }
        
        // Test 2: Check Tables
        echo "<h2>2. ตรวจสอบตารางในฐานข้อมูล</h2>";
        $requiredTables = ['raw_materials', 'daily_stock_records', 'excel_export_log'];
        
        try {
            $stmt = $db->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<table>";
            echo "<tr><th>ตารางที่ต้องการ</th><th>สถานะ</th></tr>";
            
            foreach ($requiredTables as $table) {
                $exists = in_array($table, $tables);
                $status = $exists 
                    ? '<span class="badge badge-success">มีอยู่</span>' 
                    : '<span class="badge badge-danger">ไม่พบ</span>';
                
                echo "<tr><td><code>$table</code></td><td>$status</td></tr>";
                
                if (!$exists) {
                    $allPassed = false;
                }
            }
            
            echo "</table>";
            
            if ($allPassed) {
                echo '<div class="success">✅ ตารางทั้งหมดพร้อมใช้งาน</div>';
            } else {
                echo '<div class="error">❌ ขาดตารางบางตัว กรุณานำเข้าไฟล์ database.sql</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="error">❌ ไม่สามารถตรวจสอบตารางได้: ' . $e->getMessage() . '</div>';
            $allPassed = false;
        }
        
        // Test 3: Check Raw Materials
        echo "<h2>3. ตรวจสอบข้อมูลวัตถุดิบ</h2>";
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM raw_materials WHERE is_active = 1");
            $result = $stmt->fetch();
            $count = $result['count'];
            
            if ($count > 0) {
                echo '<div class="success">✅ พบวัตถุดิบ ' . $count . ' รายการ</div>';
                
                // แสดงรายการวัตถุดิบ
                $stmt = $db->query("
                    SELECT material_name, unit, display_order 
                    FROM raw_materials 
                    WHERE is_active = 1 
                    ORDER BY display_order, material_name
                    LIMIT 10
                ");
                $materials = $stmt->fetchAll();
                
                echo "<table>";
                echo "<tr><th>#</th><th>ชื่อวัตถุดิบ</th><th>หน่วย</th><th>ลำดับ</th></tr>";
                foreach ($materials as $index => $mat) {
                    echo "<tr>";
                    echo "<td>" . ($index + 1) . "</td>";
                    echo "<td>{$mat['material_name']}</td>";
                    echo "<td>{$mat['unit']}</td>";
                    echo "<td>{$mat['display_order']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                if ($count > 10) {
                    echo '<div class="info">📝 แสดงเพียง 10 รายการแรก (มีทั้งหมด ' . $count . ' รายการ)</div>';
                }
            } else {
                echo '<div class="error">❌ ไม่พบข้อมูลวัตถุดิบในระบบ กรุณาเพิ่มข้อมูลก่อนใช้งาน</div>';
                $allPassed = false;
            }
        } catch (Exception $e) {
            echo '<div class="error">❌ ไม่สามารถตรวจสอบวัตถุดิบได้: ' . $e->getMessage() . '</div>';
            $allPassed = false;
        }
        
        // Test 4: Check Directories
        echo "<h2>4. ตรวจสอบโฟลเดอร์</h2>";
        $requiredDirs = [
            'stock-photos' => PHOTOS_DIR,
            'excel-exports' => EXCEL_DIR,
            'logs' => LOGS_DIR
        ];
        
        echo "<table>";
        echo "<tr><th>โฟลเดอร์</th><th>Path</th><th>สถานะ</th><th>Writable</th></tr>";
        
        foreach ($requiredDirs as $name => $path) {
            $exists = file_exists($path);
            $writable = $exists && is_writable($path);
            
            $statusBadge = $exists 
                ? '<span class="badge badge-success">มีอยู่</span>' 
                : '<span class="badge badge-danger">ไม่พบ</span>';
                
            $writableBadge = $writable 
                ? '<span class="badge badge-success">เขียนได้</span>' 
                : '<span class="badge badge-danger">เขียนไม่ได้</span>';
            
            echo "<tr>";
            echo "<td><strong>$name</strong></td>";
            echo "<td><code>$path</code></td>";
            echo "<td>$statusBadge</td>";
            echo "<td>$writableBadge</td>";
            echo "</tr>";
            
            if (!$exists || !$writable) {
                $allPassed = false;
            }
        }
        
        echo "</table>";
        
        if (!$allPassed) {
            echo '<div class="error">❌ โฟลเดอร์บางตัวไม่พร้อมใช้งาน<br>สร้างโฟลเดอร์: <code>mkdir -p logs stock-photos excel-exports</code><br>ตั้งค่า permission: <code>chmod 775 logs stock-photos excel-exports</code></div>';
        }
        
        // Test 5: PHP Extensions
        echo "<h2>5. ตรวจสอบ PHP Extensions</h2>";
        $requiredExtensions = ['pdo', 'pdo_mysql', 'gd', 'zip', 'xml', 'mbstring'];
        
        echo "<table>";
        echo "<tr><th>Extension</th><th>สถานะ</th></tr>";
        
        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            $status = $loaded 
                ? '<span class="badge badge-success">ติดตั้งแล้ว</span>' 
                : '<span class="badge badge-danger">ไม่พบ</span>';
            
            echo "<tr><td><code>$ext</code></td><td>$status</td></tr>";
            
            if (!$loaded) {
                $allPassed = false;
            }
        }
        
        echo "</table>";
        
        // Test 6: PhpSpreadsheet
        echo "<h2>6. ตรวจสอบ PhpSpreadsheet</h2>";
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            echo '<div class="success">✅ PhpSpreadsheet ติดตั้งแล้ว</div>';
        } else {
            echo '<div class="error">❌ PhpSpreadsheet ยังไม่ได้ติดตั้ง<br>ติดตั้ง: <code>composer require phpoffice/phpspreadsheet</code></div>';
            $allPassed = false;
        }
        
        // Test 7: Recent Records
        echo "<h2>7. บันทึกล่าสุด</h2>";
        try {
            $stmt = $db->query("
                SELECT 
                    record_date,
                    COUNT(*) as items,
                    employee_name,
                    submitted_at
                FROM daily_stock_records
                GROUP BY record_date, employee_name, submitted_at
                ORDER BY record_date DESC
                LIMIT 5
            ");
            $records = $stmt->fetchAll();
            
            if (!empty($records)) {
                echo "<table>";
                echo "<tr><th>วันที่</th><th>จำนวนรายการ</th><th>พนักงาน</th><th>เวลาบันทึก</th></tr>";
                foreach ($records as $rec) {
                    echo "<tr>";
                    echo "<td>{$rec['record_date']}</td>";
                    echo "<td>{$rec['items']} รายการ</td>";
                    echo "<td>{$rec['employee_name']}</td>";
                    echo "<td>{$rec['submitted_at']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo '<div class="info">📝 ยังไม่มีการบันทึกข้อมูลในระบบ</div>';
            }
        } catch (Exception $e) {
            echo '<div class="error">❌ ไม่สามารถตรวจสอบบันทึกได้: ' . $e->getMessage() . '</div>';
        }
        
        // Summary
        echo "<h2>📊 สรุปผลการทดสอบ</h2>";
        if ($allPassed) {
            echo '<div class="success">';
            echo '<h3>✅ ระบบพร้อมใช้งาน!</h3>';
            echo '<p>ทุกอย่างทำงานได้ปกติ คุณสามารถเริ่มใช้งานระบบได้เลย</p>';
            echo '<p><a href="index.php" style="background: #4472C4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;">ไปที่หน้าหลัก</a></p>';
            echo '</div>';
        } else {
            echo '<div class="error">';
            echo '<h3>⚠️ พบปัญหาบางอย่าง</h3>';
            echo '<p>กรุณาแก้ไขปัญหาข้างต้นก่อนใช้งานระบบ</p>';
            echo '</div>';
        }
        ?>
        
        <div class="info" style="margin-top: 30px;">
            <strong>ข้อมูลเพิ่มเติม:</strong><br>
            PHP Version: <code><?php echo PHP_VERSION; ?></code><br>
            Server Software: <code><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></code><br>
            Document Root: <code><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></code><br>
            Current Time: <code><?php echo date('Y-m-d H:i:s'); ?></code>
        </div>
    </div>
</body>
</html>

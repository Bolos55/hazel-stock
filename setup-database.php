<?php
/**
 * Database Setup Script
 * Creates all necessary tables for Hazel Stock Management
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // Load config without dying on missing env vars
    date_default_timezone_set('Asia/Bangkok');
    
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_PORT', getenv('DB_PORT') ?: 3306);
    define('DB_NAME', getenv('DB_NAME') ?: 'hazel_stock');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('DB_CHARSET', 'utf8mb4');

    // Check if we have database credentials
    if (!getenv('DB_HOST') || !getenv('DB_NAME') || !getenv('DB_USER')) {
        throw new Exception('Database environment variables not set. Please configure them on Render.com first.');
    }

    // Connect to database
    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=%s",
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // SQL to create all tables
    $sql = "
    SET NAMES utf8mb4;
    SET FOREIGN_KEY_CHECKS = 0;

    -- ================= EMPLOYEES TABLE ================= 
    CREATE TABLE IF NOT EXISTS `employees` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `full_name` varchar(255) NOT NULL COMMENT 'ชื่อเต็มพนักงาน',
        `employee_name` varchar(255) NOT NULL COMMENT 'ชื่อเรียกพนักงาน',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_full_name` (`full_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- ================= RAW MATERIALS TABLE ================= 
    CREATE TABLE IF NOT EXISTS `raw_materials` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `material_code` varchar(50) NOT NULL COMMENT 'รหัสวัตถุดิบ',
        `material_name` varchar(255) NOT NULL COMMENT 'ชื่อวัตถุดิบ',
        `unit` varchar(50) NOT NULL COMMENT 'หน่วยนับ',
        `current_stock` decimal(10,2) DEFAULT 0.00 COMMENT 'สต็อกปัจจุบัน',
        `display_order` int(11) DEFAULT 0 COMMENT 'ลำดับการแสดงผล',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_material_code` (`material_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- ================= DAILY STOCK RECORDS TABLE ================= 
    CREATE TABLE IF NOT EXISTS `daily_stock_records` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `record_date` date NOT NULL COMMENT 'วันที่บันทึก',
        `employee_id` int(11) NOT NULL COMMENT 'รหัสพนักงาน',
        `material_id` int(11) NOT NULL COMMENT 'รหัสวัตถุดิบ',
        `remaining_quantity` decimal(10,2) NOT NULL COMMENT 'จำนวนคงเหลือ',
        `photo_path` varchar(255) DEFAULT NULL COMMENT 'path รูปภาพ',
        `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'เวลาที่บันทึก',
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_daily_record` (`record_date`, `material_id`),
        KEY `idx_record_date` (`record_date`),
        KEY `idx_employee_id` (`employee_id`),
        KEY `idx_material_id` (`material_id`),
        CONSTRAINT `fk_daily_stock_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_daily_stock_material` FOREIGN KEY (`material_id`) REFERENCES `raw_materials` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- ================= EXCEL EXPORT LOG TABLE ================= 
    CREATE TABLE IF NOT EXISTS `excel_export_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `export_date` date NOT NULL COMMENT 'วันที่ export',
        `file_path` varchar(255) DEFAULT NULL COMMENT 'path ไฟล์ Excel',
        `records_count` int(11) DEFAULT 0 COMMENT 'จำนวน records',
        `status` varchar(50) DEFAULT NULL COMMENT 'สถานะ (success/failed)',
        `error_message` text DEFAULT NULL COMMENT 'ข้อความ error',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_export_date` (`export_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    SET FOREIGN_KEY_CHECKS = 1;
    ";

    // Execute the SQL
    $pdo->exec("DROP TABLE IF EXISTS daily_stock_records");
    $pdo->exec("DROP TABLE IF EXISTS excel_export_log"); 
    $pdo->exec("DROP TABLE IF EXISTS employees");
    $pdo->exec("DROP TABLE IF EXISTS raw_materials");
    
    $pdo->exec($sql);

    // Insert sample data
    $sampleDataSQL = "
    -- ================= SAMPLE DATA ================= 
    INSERT IGNORE INTO `employees` (`full_name`, `employee_name`) VALUES
    ('นายสมชาย ใจดี', 'สมชาย'),
    ('นางสาวสมหญิง รักงาน', 'สมหญิง'),
    ('นายพิชิต มานะดี', 'พิชิต');

    INSERT IGNORE INTO `raw_materials` (`material_code`, `material_name`, `unit`, `display_order`) VALUES
    ('MILK001', 'นม', 'ลิตร', 1),
    ('SUGAR001', 'น้ำตาล', 'กิโลกรัม', 2),
    ('COFFEE001', 'กาแฟ', 'กิโลกรัม', 3),
    ('TEA001', 'ชา', 'กิโลกรัม', 4),
    ('CHOC001', 'ช็อก', 'กิโลกรัม', 5),
    ('DARK001', 'ดาร์ก', 'กิโลกรัม', 6),
    ('WHITE001', 'ไวท์', 'กิโลกรัม', 7),
    ('MILK_CON001', 'นมข้น', 'กระป๋อง', 8),
    ('SYRUP_OR001', 'ไซรัปส้ม', 'ขวด', 9),
    ('SYRUP_ST001', 'ไซรัปสตรอว์เบอรี่', 'ขวด', 10),
    ('SYRUP_MI001', 'ไซรัปมิ้นท์', 'ขวด', 11),
    ('SYRUP_GO001', 'ไซรัปทอง', 'ขวด', 12),
    ('WATER001', 'น้ำเปล่า', 'ลิตร', 13),
    ('DIP001', 'ดิป', 'กิโลกรัม', 14);
    ";

    $pdo->exec($sampleDataSQL);

    // Verify tables were created
    $tables = ['employees', 'raw_materials', 'daily_stock_records', 'excel_export_log'];
    $createdTables = [];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $createdTables[] = $table;
        }
    }

    // Count sample data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees");
    $employeeCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM raw_materials");
    $materialCount = $stmt->fetch()['count'];

    echo json_encode([
        'success' => true,
        'message' => 'Database setup completed successfully!',
        'details' => [
            'tables_created' => $createdTables,
            'employees_inserted' => $employeeCount,
            'materials_inserted' => $materialCount,
            'database' => DB_NAME,
            'host' => DB_HOST
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_code' => $e->getCode(),
        'details' => [
            'host' => DB_HOST ?? 'not set',
            'database' => DB_NAME ?? 'not set',
            'user' => DB_USER ?? 'not set'
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'details' => [
            'host' => DB_HOST ?? 'not set',
            'database' => DB_NAME ?? 'not set'
        ]
    ], JSON_UNESCAPED_UNICODE);
}
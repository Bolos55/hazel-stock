<?php
/**
 * MySQL Database Setup Script
 * สร้างตารางสำหรับ MySQL (ใช้แทน PostgreSQL)
 */

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    echo "🚀 Setting up MySQL Database...\n\n";
    
    $db = Database::getInstance()->getConnection();
    echo "✅ Database connection successful!\n\n";
    
    // Create employees table
    echo "📋 Creating employees table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS employees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'employee',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Employees table created!\n";
    
    // Create raw_materials table
    echo "📋 Creating raw_materials table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS raw_materials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            material_name VARCHAR(255) NOT NULL,
            unit VARCHAR(50) NOT NULL,
            sub_unit VARCHAR(50) DEFAULT '',
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Raw materials table created!\n";
    
    // Create daily_stock_records table
    echo "📋 Creating daily_stock_records table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS daily_stock_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            material_id INT,
            employee_id INT,
            quantity DECIMAL(10,2) NOT NULL,
            sub_quantity DECIMAL(10,2) DEFAULT 0,
            photo_path VARCHAR(500),
            work_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (material_id) REFERENCES raw_materials(id) ON DELETE CASCADE,
            FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Daily stock records table created!\n";
    
    // Create stock_additions table
    echo "📋 Creating stock_additions table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS stock_additions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            material_id INT,
            employee_id INT,
            quantity DECIMAL(10,2) NOT NULL,
            sub_quantity DECIMAL(10,2) DEFAULT 0,
            note TEXT,
            work_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (material_id) REFERENCES raw_materials(id) ON DELETE CASCADE,
            FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Stock additions table created!\n";
    
    // Insert admin user if not exists
    echo "👤 Creating admin user...\n";
    $stmt = $db->prepare("SELECT COUNT(*) FROM employees WHERE email = ?");
    $stmt->execute(['bosszazababa@gmail.com']);
    
    if ($stmt->fetchColumn() == 0) {
        $stmt = $db->prepare("
            INSERT INTO employees (name, email, password, role) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            'ภูริวัฒน์ โภคสวัสดิ์',
            'bosszazababa@gmail.com',
            password_hash('Bossmaha_2003', PASSWORD_DEFAULT),
            'admin'
        ]);
        echo "✅ Admin user created!\n";
    } else {
        echo "ℹ️ Admin user already exists!\n";
    }
    
    // Insert sample materials if table is empty
    echo "📦 Checking materials...\n";
    $stmt = $db->query("SELECT COUNT(*) FROM raw_materials");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "📦 Adding sample materials...\n";
        $materials = [
            ['แป้งสาลี', 'กิโลกรัม', 'กรัม', 1],
            ['น้ำตาล', 'กิโลกรัม', 'กรัม', 2],
            ['เนื้อหมู', 'กิโลกรัม', 'กรัม', 3],
            ['ไข่ไก่', 'ฟอง', '', 4],
            ['น้ำมันพืช', 'ลิตร', 'มิลลิลิตร', 5]
        ];
        
        $stmt = $db->prepare("
            INSERT INTO raw_materials (material_name, unit, sub_unit, display_order) 
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($materials as $material) {
            $stmt->execute($material);
        }
        echo "✅ Sample materials added!\n";
    } else {
        echo "ℹ️ Materials already exist ({$count} items)!\n";
    }
    
    echo "\n🎉 Database setup completed successfully!\n";
    echo "👤 Admin Login: bosszazababa@gmail.com / Bossmaha_2003\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
    echo "📍 Trace: " . $e->getTraceAsString() . "\n";
}
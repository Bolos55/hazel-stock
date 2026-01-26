<?php
/**
 * Setup PostgreSQL Database for Hazel Stock Management
 * Run this after creating PostgreSQL database on Render
 */

require_once 'config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "🐘 Setting up PostgreSQL database...\n\n";
    
    // 1. Create employees table
    echo "👥 Creating employees table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS employees (
            id SERIAL PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            first_name VARCHAR(255),
            last_name VARCHAR(255),
            employee_name VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'employee',
            username VARCHAR(255),
            password VARCHAR(255),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(full_name),
            UNIQUE(username)
        )
    ");
    echo "   ✓ Created employees table\n";
    
    // 2. Create raw_materials table
    echo "🧪 Creating raw_materials table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS raw_materials (
            id SERIAL PRIMARY KEY,
            material_code VARCHAR(50) NOT NULL,
            material_name VARCHAR(255) NOT NULL,
            unit VARCHAR(50) NOT NULL,
            sub_unit VARCHAR(50),
            unit_quantity DECIMAL(10,2) DEFAULT 0.00,
            sub_unit_quantity DECIMAL(10,2) DEFAULT 0.00,
            current_stock DECIMAL(10,2) DEFAULT 0.00,
            display_order INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(material_code)
        )
    ");
    echo "   ✓ Created raw_materials table\n";
    
    // 3. Create daily_stock_records table
    echo "📊 Creating daily_stock_records table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS daily_stock_records (
            id SERIAL PRIMARY KEY,
            record_date DATE NOT NULL,
            employee_id INTEGER NOT NULL,
            employee_name VARCHAR(255),
            material_id INTEGER NOT NULL,
            remaining_quantity DECIMAL(10,2) NOT NULL,
            quantity_main DECIMAL(10,2) DEFAULT 0.00,
            quantity_sub DECIMAL(10,2) DEFAULT 0.00,
            photo_path VARCHAR(255),
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
            FOREIGN KEY (material_id) REFERENCES raw_materials(id) ON DELETE CASCADE,
            UNIQUE(record_date, material_id)
        )
    ");
    echo "   ✓ Created daily_stock_records table\n";
    
    // 4. Create stock_additions table
    echo "📦 Creating stock_additions table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS stock_additions (
            id SERIAL PRIMARY KEY,
            material_id INTEGER NOT NULL,
            employee_id INTEGER NOT NULL,
            quantity DECIMAL(10,2) NOT NULL,
            note VARCHAR(255),
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (material_id) REFERENCES raw_materials(id) ON DELETE CASCADE,
            FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        )
    ");
    echo "   ✓ Created stock_additions table\n";
    
    // 5. Create admin user
    echo "👑 Creating admin user...\n";
    $hashedPassword = password_hash('Bossmaha_2003', PASSWORD_DEFAULT);
    $stmt = $db->prepare("
        INSERT INTO employees (full_name, first_name, last_name, employee_name, role, username, password) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON CONFLICT (username) DO NOTHING
    ");
    $stmt->execute([
        'ภูริวัฒน์ โภคสวัสดิ์',
        'ภูริวัฒน์',
        'โภคสวัสดิ์',
        'Boss',
        'admin',
        'bosszazababa@gmail.com',
        $hashedPassword
    ]);
    echo "   ✓ Created admin user: bosszazababa@gmail.com\n";
    
    // 6. Insert sample materials
    echo "🧪 Inserting sample materials...\n";
    $materials = [
        ['MILK001', 'นม', 'ถุง', 'ลิตร', 1],
        ['SUGAR001', 'น้ำตาล', 'ถุง', 'กิโลกรัม', 2],
        ['COFFEE001', 'กาแฟ', 'ถุง', 'กิโลกรัม', 3],
        ['TEA001', 'ชา', 'ถุง', 'กิโลกรัม', 4],
        ['CHOC001', 'ช็อก', 'ถุง', 'กิโลกรัม', 5],
        ['DARK001', 'ดาร์ก', 'ถุง', 'กิโลกรัม', 6],
        ['WHITE001', 'ไวท์', 'ถุง', 'กิโลกรัม', 7],
        ['MILK_CON001', 'นมข้น', 'กระป๋อง', 'มิลลิลิตร', 8],
        ['SYRUP_OR001', 'ไซรัปส้ม', 'ขวด', 'มิลลิลิตร', 9],
        ['SYRUP_ST001', 'ไซรัปสตรอว์เบอรี่', 'ขวด', 'มิลลิลิตร', 10],
        ['SYRUP_MI001', 'ไซรัปมิ้นท์', 'ขวด', 'มิลลิลิตร', 11],
        ['SYRUP_GO001', 'ไซรัปทอง', 'ขวด', 'มิลลิลิตร', 12],
        ['WATER001', 'น้ำเปล่า', 'ขวด', 'ลิตร', 13],
        ['DIP001', 'ดิป', 'ถุง', 'กิโลกรัม', 14]
    ];
    
    $stmt = $db->prepare("
        INSERT INTO raw_materials (material_code, material_name, unit, sub_unit, display_order) 
        VALUES (?, ?, ?, ?, ?)
        ON CONFLICT (material_code) DO NOTHING
    ");
    
    foreach ($materials as $material) {
        $stmt->execute($material);
    }
    echo "   ✓ Inserted " . count($materials) . " materials\n";
    
    echo "\n✅ PostgreSQL database setup complete!\n\n";
    echo "📋 Summary:\n";
    echo "   - Created all tables\n";
    echo "   - Created admin user: bosszazababa@gmail.com / Bossmaha_2003\n";
    echo "   - Inserted sample materials\n\n";
    
    echo "🎯 Next steps:\n";
    echo "   1. Test login at: https://hazel-stock.onrender.com/login.php\n";
    echo "   2. Username: bosszazababa@gmail.com\n";
    echo "   3. Password: Bossmaha_2003\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
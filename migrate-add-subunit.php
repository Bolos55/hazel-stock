<?php
require_once 'config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "🔄 เริ่มการอัพเดทฐานข้อมูล...\n";
    
    // Check if sub_unit column exists
    $stmt = $db->query("SHOW COLUMNS FROM raw_materials LIKE 'sub_unit'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        echo "➕ เพิ่มคอลัมน์ sub_unit...\n";
        $db->exec("ALTER TABLE raw_materials ADD COLUMN sub_unit VARCHAR(50) DEFAULT NULL COMMENT 'หน่วยย่อย' AFTER unit");
        
        // Update existing materials with sample sub_units
        $updates = [
            'MILK001' => ['unit' => 'ถุง', 'sub_unit' => 'ลิตร'],
            'SUGAR001' => ['unit' => 'ถุง', 'sub_unit' => 'กิโลกรัม'],
            'COFFEE001' => ['unit' => 'ถุง', 'sub_unit' => 'กิโลกรัม'],
            'TEA001' => ['unit' => 'ถุง', 'sub_unit' => 'กิโลกรัม'],
            'CHOC001' => ['unit' => 'ถุง', 'sub_unit' => 'กิโลกรัม'],
            'DARK001' => ['unit' => 'ถุง', 'sub_unit' => 'กิโลกรัม'],
            'WHITE001' => ['unit' => 'ถุง', 'sub_unit' => 'กิโลกรัม'],
            'MILK_CON001' => ['unit' => 'กระป๋อง', 'sub_unit' => 'มิลลิลิตร'],
            'SYRUP_OR001' => ['unit' => 'ขวด', 'sub_unit' => 'มิลลิลิตร'],
            'SYRUP_ST001' => ['unit' => 'ขวด', 'sub_unit' => 'มิลลิลิตร'],
            'SYRUP_MI001' => ['unit' => 'ขวด', 'sub_unit' => 'มิลลิลิตร'],
            'SYRUP_GO001' => ['unit' => 'ขวด', 'sub_unit' => 'มิลลิลิตร'],
            'WATER001' => ['unit' => 'ขวด', 'sub_unit' => 'ลิตร'],
            'DIP001' => ['unit' => 'ถุง', 'sub_unit' => 'กิโลกรัม']
        ];
        
        echo "🔄 อัพเดทข้อมูลวัตถุดิบที่มีอยู่...\n";
        $stmt = $db->prepare("UPDATE raw_materials SET unit = ?, sub_unit = ? WHERE material_code = ?");
        
        foreach ($updates as $code => $data) {
            $stmt->execute([$data['unit'], $data['sub_unit'], $code]);
            echo "   ✅ อัพเดท {$code}\n";
        }
        
        echo "✅ เพิ่มคอลัมน์ sub_unit สำเร็จ!\n";
    } else {
        echo "ℹ️ คอลัมน์ sub_unit มีอยู่แล้ว\n";
    }
    
    // Check if stock_additions table exists
    $stmt = $db->query("SHOW TABLES LIKE 'stock_additions'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "➕ สร้างตาราง stock_additions...\n";
        $db->exec("
            CREATE TABLE stock_additions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                material_id INT NOT NULL,
                employee_id INT NOT NULL,
                quantity DECIMAL(10,2) NOT NULL COMMENT 'จำนวนที่เพิ่ม',
                note VARCHAR(255) DEFAULT NULL COMMENT 'หมายเหตุ',
                added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'เวลาที่เพิ่ม',
                KEY idx_material_id (material_id),
                KEY idx_employee_id (employee_id),
                KEY idx_added_at (added_at),
                CONSTRAINT fk_stock_additions_material FOREIGN KEY (material_id) REFERENCES raw_materials (id) ON DELETE CASCADE,
                CONSTRAINT fk_stock_additions_employee FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✅ สร้างตาราง stock_additions สำเร็จ!\n";
    } else {
        echo "ℹ️ ตาราง stock_additions มีอยู่แล้ว\n";
    }
    
    // Show current materials
    echo "\n📋 รายการวัตถุดิบปัจจุบัน:\n";
    $stmt = $db->query("SELECT material_code, material_name, unit, sub_unit FROM raw_materials ORDER BY display_order");
    $materials = $stmt->fetchAll();
    
    foreach ($materials as $material) {
        $unitDisplay = $material['unit'];
        if ($material['sub_unit']) {
            $unitDisplay .= ' - ' . $material['sub_unit'];
        }
        echo "   • {$material['material_name']} ({$unitDisplay})\n";
    }
    
    echo "\n🎉 การอัพเดทเสร็จสิ้น!\n";
    
} catch (Exception $e) {
    echo "❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "\n";
}
?>
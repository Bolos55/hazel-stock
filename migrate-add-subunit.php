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
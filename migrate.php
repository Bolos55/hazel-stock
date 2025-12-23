<?php
require_once 'config.php';

$output = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $output[] = "🔄 เริ่มการอัพเดทฐานข้อมูล...";
        
        // Check if sub_unit column exists
        $stmt = $db->query("SHOW COLUMNS FROM raw_materials LIKE 'sub_unit'");
        $columnExists = $stmt->rowCount() > 0;
        
        if (!$columnExists) {
            $output[] = "➕ เพิ่มคอลัมน์ sub_unit...";
            $db->exec("ALTER TABLE raw_materials ADD COLUMN sub_unit VARCHAR(50) DEFAULT NULL COMMENT 'หน่วยย่อย' AFTER unit");
        }
        
        // Check if quantity columns exist
        $stmt = $db->query("SHOW COLUMNS FROM raw_materials LIKE 'unit_quantity'");
        $unitQuantityExists = $stmt->rowCount() > 0;
        
        $stmt = $db->query("SHOW COLUMNS FROM raw_materials LIKE 'sub_unit_quantity'");
        $subUnitQuantityExists = $stmt->rowCount() > 0;
        
        if (!$unitQuantityExists) {
            $output[] = "➕ เพิ่มคอลัมน์ unit_quantity...";
            $db->exec("ALTER TABLE raw_materials ADD COLUMN unit_quantity DECIMAL(10,2) DEFAULT 0.00 COMMENT 'จำนวนหน่วยหลัก' AFTER sub_unit");
        }
        
        if (!$subUnitQuantityExists) {
            $output[] = "➕ เพิ่มคอลัมน์ sub_unit_quantity...";
            $db->exec("ALTER TABLE raw_materials ADD COLUMN sub_unit_quantity DECIMAL(10,2) DEFAULT 0.00 COMMENT 'จำนวนหน่วยย่อย' AFTER unit_quantity");
        }
        
        if (!$columnExists) {
            
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
            
            $output[] = "🔄 อัพเดทข้อมูลวัตถุดิบที่มีอยู่...";
            $stmt = $db->prepare("UPDATE raw_materials SET unit = ?, sub_unit = ? WHERE material_code = ?");
            
            foreach ($updates as $code => $data) {
                $stmt->execute([$data['unit'], $data['sub_unit'], $code]);
                $output[] = "   ✅ อัพเดท {$code}";
            }
            
            $output[] = "✅ เพิ่มคอลัมน์ sub_unit สำเร็จ!";
        } else {
            $output[] = "ℹ️ คอลัมน์ sub_unit มีอยู่แล้ว";
        }
        
        if (!$unitQuantityExists) {
            $output[] = "ℹ️ คอลัมน์ unit_quantity มีอยู่แล้ว";
        }
        
        if (!$subUnitQuantityExists) {
            $output[] = "ℹ️ คอลัมน์ sub_unit_quantity มีอยู่แล้ว";
        }
        
        // Check if stock_additions table exists
        $stmt = $db->query("SHOW TABLES LIKE 'stock_additions'");
        $tableExists = $stmt->rowCount() > 0;
        
        if (!$tableExists) {
            $output[] = "➕ สร้างตาราง stock_additions...";
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
            $output[] = "✅ สร้างตาราง stock_additions สำเร็จ!";
        } else {
            $output[] = "ℹ️ ตาราง stock_additions มีอยู่แล้ว";
        }
        
        // Show current materials
        $output[] = "";
        $output[] = "📋 รายการวัตถุดิบปัจจุบัน:";
        $stmt = $db->query("SELECT material_code, material_name, unit, sub_unit FROM raw_materials ORDER BY display_order");
        $materials = $stmt->fetchAll();
        
        foreach ($materials as $material) {
            $unitDisplay = $material['unit'];
            if ($material['sub_unit']) {
                $unitDisplay .= ' - ' . $material['sub_unit'];
            }
            $output[] = "   • {$material['material_name']} ({$unitDisplay})";
        }
        
        $output[] = "";
        $output[] = "🎉 การอัพเดทเสร็จสิ้น!";
        $success = true;
        
    } catch (Exception $e) {
        $output[] = "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อัพเดทฐานข้อมูล - Hazel</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .migration-output {
            background: #1f2937;
            color: #f9fafb;
            padding: 1.5rem;
            border-radius: 0.5rem;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            line-height: 1.5;
            white-space: pre-line;
            max-height: 400px;
            overflow-y: auto;
        }
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .alert-info {
            background: #eff6ff;
            border: 1px solid #3b82f6;
            color: #1e40af;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #10b981;
            color: #065f46;
        }
        .alert-warning {
            background: #fffbeb;
            border: 1px solid #f59e0b;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="app-header hazel-header">
            <img src="assets/hazel-logo.png" alt="Hazel" class="hazel-logo">
            <div class="hazel-subtitle">Beverages & Appetizers</div>
            <h1>อัพเดทฐานข้อมูล</h1>
        </div>
        
        <div class="employee-section">
            <!-- Navigation -->
            <div class="material-card mb-4">
                <div class="flex justify-between items-center">
                    <a href="/" class="text-blue-600 hover:text-blue-800">← กลับหน้าหลัก</a>
                    <div class="space-x-2">
                        <a href="/setup.php" class="text-gray-600 hover:text-gray-800 text-sm">🛠️ Setup</a>
                    </div>
                </div>
            </div>

            <?php if (empty($output)): ?>
                <!-- Migration Form -->
                <div class="material-card">
                    <h3 class="text-lg font-semibold mb-4">🔄 อัพเดทฐานข้อมูล</h3>
                    
                    <div class="alert alert-info">
                        <h4 class="font-semibold mb-2">📋 การอัพเดทนี้จะทำ:</h4>
                        <ul class="list-disc list-inside space-y-1">
                            <li>เพิ่มคอลัมน์ <code>sub_unit</code> ในตาราง raw_materials</li>
                            <li>เพิ่มคอลัมน์ <code>unit_quantity</code> และ <code>sub_unit_quantity</code> สำหรับระบบจำนวนคู่</li>
                            <li>อัพเดทข้อมูลวัตถุดิบที่มีอยู่ให้มีหน่วยย่อย</li>
                            <li>สร้างตาราง <code>stock_additions</code> สำหรับระบบเพิ่มสต็อก</li>
                            <li>ตรวจสอบและแสดงข้อมูลปัจจุบัน</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning">
                        <p><strong>⚠️ คำเตือน:</strong> การอัพเดทนี้จะเปลี่ยนแปลงโครงสร้างฐานข้อมูล กรุณาแน่ใจก่อนดำเนินการ</p>
                    </div>
                    
                    <form method="POST">
                        <button type="submit" name="run_migration" class="btn-primary" 
                                onclick="return confirm('คุณแน่ใจหรือไม่ที่จะอัพเดทฐานข้อมูล?')">
                            🚀 เริ่มอัพเดทฐานข้อมูล
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Migration Results -->
                <div class="material-card">
                    <h3 class="text-lg font-semibold mb-4">
                        <?= $success ? '✅ อัพเดทสำเร็จ' : '❌ เกิดข้อผิดพลาด' ?>
                    </h3>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <p><strong>🎉 อัพเดทฐานข้อมูลสำเร็จ!</strong> ตอนนี้สามารถใช้งานฟีเจอร์ใหม่ได้แล้ว</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="migration-output">
<?= implode("\n", $output) ?>
                    </div>
                    
                    <div class="mt-4 space-x-2">
                        <a href="/" class="btn-primary">🏠 กลับหน้าหลัก</a>
                        <a href="/manage-materials.php" class="btn-secondary">🧪 จัดการวัตถุดิบ</a>
                        <a href="/add-stock.php" class="btn-secondary">📦 เพิ่มสต็อกเข้า</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
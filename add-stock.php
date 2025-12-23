<?php
require_once 'config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance()->getConnection();
        
        if (isset($_POST['action']) && $_POST['action'] === 'add_stock') {
            $materialId = (int)$_POST['material_id'];
            $quantity = (float)$_POST['quantity'];
            $note = trim($_POST['note']) ?: null;
            $employeeName = trim($_POST['employee_name']);
            
            if (empty($employeeName)) {
                throw new Exception('กรุณากรอกชื่อพนักงาน');
            }
            
            if ($quantity <= 0) {
                throw new Exception('กรุณากรอกจำนวนที่ถูกต้อง');
            }
            
            // Get or create employee
            $stmt = $db->prepare("SELECT id FROM employees WHERE employee_name = ?");
            $stmt->execute([$employeeName]);
            $employee = $stmt->fetch();
            
            if (!$employee) {
                $stmt = $db->prepare("INSERT INTO employees (full_name, employee_name) VALUES (?, ?)");
                $stmt->execute([$employeeName, $employeeName]);
                $employeeId = $db->lastInsertId();
            } else {
                $employeeId = $employee['id'];
            }
            
            // Add stock entry (only if table exists)
            try {
                $stmt = $db->prepare("
                    INSERT INTO stock_additions (material_id, employee_id, quantity, note, added_at) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$materialId, $employeeId, $quantity, $note]);
                $success = "เพิ่มสต็อกสำเร็จ: {$quantity} หน่วย";
            } catch (Exception $e) {
                if (strpos($e->getMessage(), "stock_additions") !== false) {
                    throw new Exception('ตาราง stock_additions ยังไม่ได้สร้าง กรุณาไปที่หน้า "🔄 อัพเดท DB" ก่อน');
                } else {
                    throw $e;
                }
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get all materials
try {
    $db = Database::getInstance()->getConnection();
    
    // Check if sub_unit column exists
    try {
        $stmt = $db->query("
            SELECT id, material_name, unit, COALESCE(sub_unit, '') as sub_unit 
            FROM raw_materials 
            ORDER BY display_order ASC, material_name ASC
        ");
    } catch (Exception $e) {
        // Fallback if sub_unit doesn't exist
        $stmt = $db->query("
            SELECT id, material_name, unit, '' as sub_unit 
            FROM raw_materials 
            ORDER BY display_order ASC, material_name ASC
        ");
    }
    $materials = $stmt->fetchAll();
    
    // Get recent stock additions (only if table exists)
    try {
        $stmt = $db->query("
            SELECT 
                sa.quantity,
                sa.note,
                sa.added_at,
                rm.material_name,
                rm.unit,
                COALESCE(rm.sub_unit, '') as sub_unit,
                e.employee_name
            FROM stock_additions sa
            JOIN raw_materials rm ON sa.material_id = rm.id
            JOIN employees e ON sa.employee_id = e.id
            ORDER BY sa.added_at DESC
            LIMIT 10
        ");
        $recentAdditions = $stmt->fetchAll();
    } catch (Exception $e) {
        // Table doesn't exist yet
        $recentAdditions = [];
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
    $materials = [];
    $recentAdditions = [];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสต็อกเข้า - Hazel</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .stock-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .stock-table th,
        .stock-table td {
            border: 1px solid #d1d5db;
            padding: 0.75rem;
            text-align: left;
        }
        .stock-table th {
            background: #f9fafb;
            font-weight: 600;
        }
        .stock-table tr:hover {
            background: #f9fafb;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #10b981;
            color: #065f46;
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #ef4444;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="app-header hazel-header">
            <img src="assets/hazel-logo.png" alt="Hazel" class="hazel-logo">
            <div class="hazel-subtitle">Beverages & Appetizers</div>
            <h1>เพิ่มสต็อกเข้า</h1>
        </div>
        
        <div class="employee-section">
            <!-- Navigation -->
            <div class="material-card mb-4">
                <div class="flex justify-between items-center">
                    <a href="/" class="text-blue-600 hover:text-blue-800">← กลับหน้าหลัก</a>
                    <div class="space-x-2">
                        <a href="/view-records.php" class="text-blue-600 hover:text-blue-800 text-sm">📊 ดูข้อมูลสต็อก</a>
                        <a href="/manage-employees.php" class="text-green-600 hover:text-green-800 text-sm">👥 จัดการพนักงาน</a>
                        <a href="/manage-materials.php" class="text-purple-600 hover:text-purple-800 text-sm">🧪 จัดการวัตถุดิบ</a>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <?php if (isset($success)): ?>
                <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Add Stock Form -->
            <div class="material-card mb-6">
                <h3 class="text-lg font-semibold mb-4">📦 เพิ่มสต็อกเข้าระบบ</h3>
                
                <form method="POST">
                    <input type="hidden" name="action" value="add_stock">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="employee_name">ชื่อพนักงาน</label>
                            <input type="text" 
                                   id="employee_name" 
                                   name="employee_name" 
                                   class="form-input" 
                                   placeholder="ชื่อผู้รับสต็อก"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="material_id">วัตถุดิบ</label>
                            <select id="material_id" name="material_id" class="form-input" required>
                                <option value="">เลือกวัตถุดิบ</option>
                                <?php foreach ($materials as $material): ?>
                                    <option value="<?= $material['id'] ?>">
                                        <?= htmlspecialchars($material['material_name']) ?>
                                        (<?= htmlspecialchars($material['unit']) ?>
                                        <?php if ($material['sub_unit']): ?>
                                            - <?= htmlspecialchars($material['sub_unit']) ?>
                                        <?php endif; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="quantity">จำนวนที่เพิ่ม</label>
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   class="form-input" 
                                   step="0.01"
                                   min="0.01"
                                   placeholder="จำนวน"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="note">หมายเหตุ (ไม่บังคับ)</label>
                            <input type="text" 
                                   id="note" 
                                   name="note" 
                                   class="form-input" 
                                   placeholder="เช่น ซื้อเพิ่ม, ของแถม">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn-primary">📦 เพิ่มสต็อกเข้าระบบ</button>
                    </div>
                </form>
            </div>

            <!-- Recent Additions -->
            <div class="material-card">
                <?php 
                $totalAdditions = 0;
                $todayAdditions = 0;
                try {
                    $stmt = $db->query("SELECT COUNT(*) FROM stock_additions");
                    $totalAdditions = $stmt->fetchColumn();
                    
                    $stmt = $db->prepare("SELECT COUNT(*) FROM stock_additions WHERE DATE(added_at) = CURDATE()");
                    $stmt->execute();
                    $todayAdditions = $stmt->fetchColumn();
                } catch (Exception $e) {
                    // Table doesn't exist
                }
                ?>
                <h3 class="text-lg font-semibold mb-2">
                    📋 การเพิ่มสต็อกล่าสุด 
                </h3>
                <div class="text-sm text-gray-600 mb-4">
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded mr-2">
                        📊 ทั้งหมด: <?= $totalAdditions ?> รายการ
                    </span>
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded mr-2">
                        📅 วันนี้: <?= $todayAdditions ?> รายการ
                    </span>
                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded">
                        👁️ แสดง: <?= count($recentAdditions) ?> รายการล่าสุด
                    </span>
                </div>
                
                <?php if (empty($recentAdditions)): ?>
                    <div class="text-center py-8">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📦</div>
                        <p class="text-gray-600">ยังไม่มีการเพิ่มสต็อก</p>
                        <p class="text-sm text-gray-500 mt-2">
                            <?php 
                            // Check if table exists
                            try {
                                $db->query("SELECT 1 FROM stock_additions LIMIT 1");
                                echo "เริ่มเพิ่มสต็อกเพื่อดูประวัติที่นี่";
                            } catch (Exception $e) {
                                echo 'ตาราง stock_additions ยังไม่ได้สร้าง กรุณาไปที่ <a href="/migrate.php" class="text-blue-600 hover:text-blue-800">🔄 อัพเดท DB</a> ก่อน';
                            }
                            ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="stock-table">
                            <thead>
                                <tr>
                                    <th>วันที่/เวลา</th>
                                    <th>วัตถุดิบ</th>
                                    <th>จำนวน</th>
                                    <th>หน่วย</th>
                                    <th>พนักงาน</th>
                                    <th>หมายเหตุ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentAdditions as $addition): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($addition['added_at'])) ?></td>
                                        <td class="font-semibold"><?= htmlspecialchars($addition['material_name']) ?></td>
                                        <td class="text-right font-mono text-green-600">+<?= number_format($addition['quantity'], 2) ?></td>
                                        <td>
                                            <?= htmlspecialchars($addition['unit']) ?>
                                            <?php if ($addition['sub_unit']): ?>
                                                <br><span class="text-xs text-gray-600"><?= htmlspecialchars($addition['sub_unit']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($addition['employee_name']) ?></td>
                                        <td>
                                            <?php if ($addition['note']): ?>
                                                <?= htmlspecialchars($addition['note']) ?>
                                            <?php else: ?>
                                                <span class="text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($totalAdditions > count($recentAdditions)): ?>
                        <div class="mt-4 text-center">
                            <p class="text-sm text-gray-600 mb-2">
                                มีข้อมูลเพิ่มเติมอีก <?= $totalAdditions - count($recentAdditions) ?> รายการ
                            </p>
                            <button onclick="showAllAdditions()" class="btn-secondary text-sm">
                                📋 ดูทั้งหมด (<?= $totalAdditions ?> รายการ)
                            </button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showAllAdditions() {
            if (confirm('ต้องการดูข้อมูลการเพิ่มสต็อกทั้งหมดหรือไม่?\n\n(จะเปิดหน้าใหม่)')) {
                // Create a simple page to show all additions
                window.open('/view-stock-additions.php', '_blank');
            }
        }
    </script>
</body>
</html>
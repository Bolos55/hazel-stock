<?php
require_once 'config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance()->getConnection();
        
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $materialCode = trim($_POST['material_code']);
                    $materialName = trim($_POST['material_name']);
                    $unit = trim($_POST['unit']);
                    $displayOrder = (int)$_POST['display_order'];
                    
                    if (empty($materialCode) || empty($materialName) || empty($unit)) {
                        throw new Exception('กรุณากรอกข้อมูลให้ครบ');
                    }
                    
                    $stmt = $db->prepare("INSERT INTO raw_materials (material_code, material_name, unit, display_order) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$materialCode, $materialName, $unit, $displayOrder]);
                    $success = "เพิ่มวัตถุดิบสำเร็จ";
                    break;
                    
                case 'edit':
                    $id = (int)$_POST['id'];
                    $materialCode = trim($_POST['material_code']);
                    $materialName = trim($_POST['material_name']);
                    $unit = trim($_POST['unit']);
                    $displayOrder = (int)$_POST['display_order'];
                    
                    if (empty($materialCode) || empty($materialName) || empty($unit)) {
                        throw new Exception('กรุณากรอกข้อมูลให้ครบ');
                    }
                    
                    $stmt = $db->prepare("UPDATE raw_materials SET material_code = ?, material_name = ?, unit = ?, display_order = ? WHERE id = ?");
                    $stmt->execute([$materialCode, $materialName, $unit, $displayOrder, $id]);
                    $success = "แก้ไขวัตถุดิบสำเร็จ";
                    break;
                    
                case 'delete':
                    $id = (int)$_POST['id'];
                    
                    // Check if material has records
                    $stmt = $db->prepare("SELECT COUNT(*) FROM daily_stock_records WHERE material_id = ?");
                    $stmt->execute([$id]);
                    $recordCount = $stmt->fetchColumn();
                    
                    if ($recordCount > 0) {
                        throw new Exception("ไม่สามารถลบได้ เพราะวัตถุดิบนี้มีข้อมูลการบันทึกแล้ว ({$recordCount} รายการ)");
                    }
                    
                    $stmt = $db->prepare("DELETE FROM raw_materials WHERE id = ?");
                    $stmt->execute([$id]);
                    $success = "ลบวัตถุดิบสำเร็จ";
                    break;
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get all materials
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("
        SELECT 
            rm.*,
            COUNT(dsr.id) as record_count,
            MAX(dsr.record_date) as last_record_date
        FROM raw_materials rm
        LEFT JOIN daily_stock_records dsr ON rm.id = dsr.material_id
        GROUP BY rm.id
        ORDER BY rm.display_order ASC, rm.material_name ASC
    ");
    $materials = $stmt->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
    $materials = [];
}

// Get material for editing
$editMaterial = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($materials as $mat) {
        if ($mat['id'] == $editId) {
            $editMaterial = $mat;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการวัตถุดิบ - Hazel</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .material-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .material-table th,
        .material-table td {
            border: 1px solid #d1d5db;
            padding: 0.75rem;
            text-align: left;
        }
        .material-table th {
            background: #f9fafb;
            font-weight: 600;
        }
        .material-table tr:hover {
            background: #f9fafb;
        }
        .btn-small {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            margin: 0 0.25rem;
        }
        .btn-edit {
            background: #3b82f6;
            color: white;
        }
        .btn-edit:hover {
            background: #2563eb;
        }
        .btn-delete {
            background: #ef4444;
            color: white;
        }
        .btn-delete:hover {
            background: #dc2626;
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
        .grid {
            display: grid;
        }
        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }
        .grid-cols-4 {
            grid-template-columns: repeat(4, 1fr);
        }
        .gap-4 {
            gap: 1rem;
        }
        @media (max-width: 768px) {
            .grid-cols-2, .grid-cols-4 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="app-header hazel-header">
            <img src="assets/hazel-logo.png" alt="Hazel" class="hazel-logo">
            <div class="hazel-subtitle">Beverages & Appetizers</div>
            <h1>จัดการวัตถุดิบ</h1>
        </div>
        
        <div class="employee-section">
            <!-- Navigation -->
            <div class="material-card mb-4">
                <div class="flex justify-between items-center">
                    <a href="/" class="text-blue-600 hover:text-blue-800">← กลับหน้าหลัก</a>
                    <div class="space-x-2">
                        <a href="/view-records.php" class="text-blue-600 hover:text-blue-800 text-sm">📊 ดูข้อมูลสต็อก</a>
                        <a href="/manage-employees.php" class="text-green-600 hover:text-green-800 text-sm">👥 จัดการพนักงาน</a>
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

            <!-- Add/Edit Material Form -->
            <div class="material-card mb-6">
                <h3 class="text-lg font-semibold mb-4">
                    <?= $editMaterial ? '✏️ แก้ไขวัตถุดิบ' : '➕ เพิ่มวัตถุดิบใหม่' ?>
                </h3>
                
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $editMaterial ? 'edit' : 'add' ?>">
                    <?php if ($editMaterial): ?>
                        <input type="hidden" name="id" value="<?= $editMaterial['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-4 gap-4">
                        <div class="form-group">
                            <label for="material_code">รหัสวัตถุดิบ</label>
                            <input type="text" 
                                   id="material_code" 
                                   name="material_code" 
                                   class="form-input" 
                                   value="<?= htmlspecialchars($editMaterial['material_code'] ?? '') ?>"
                                   placeholder="เช่น MILK001"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="material_name">ชื่อวัตถุดิบ</label>
                            <input type="text" 
                                   id="material_name" 
                                   name="material_name" 
                                   class="form-input" 
                                   value="<?= htmlspecialchars($editMaterial['material_name'] ?? '') ?>"
                                   placeholder="เช่น นม"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="unit">หน่วย</label>
                            <input type="text" 
                                   id="unit" 
                                   name="unit" 
                                   class="form-input" 
                                   value="<?= htmlspecialchars($editMaterial['unit'] ?? '') ?>"
                                   placeholder="เช่น ลิตร"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="display_order">ลำดับแสดง</label>
                            <input type="number" 
                                   id="display_order" 
                                   name="display_order" 
                                   class="form-input" 
                                   value="<?= htmlspecialchars($editMaterial['display_order'] ?? count($materials) + 1) ?>"
                                   min="1"
                                   required>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn-primary">
                            <?= $editMaterial ? '💾 บันทึกการแก้ไข' : '➕ เพิ่มวัตถุดิบ' ?>
                        </button>
                        <?php if ($editMaterial): ?>
                            <a href="/manage-materials.php" class="btn-secondary ml-2">❌ ยกเลิก</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Material List -->
            <div class="material-card">
                <h3 class="text-lg font-semibold mb-4">🧪 รายการวัตถุดิบ (<?= count($materials) ?> รายการ)</h3>
                
                <?php if (empty($materials)): ?>
                    <div class="text-center py-8">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🧪</div>
                        <p class="text-gray-600">ยังไม่มีวัตถุดิบในระบบ</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="material-table">
                            <thead>
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>รหัส</th>
                                    <th>ชื่อวัตถุดิบ</th>
                                    <th>หน่วย</th>
                                    <th>จำนวนการบันทึก</th>
                                    <th>บันทึกล่าสุด</th>
                                    <th>วันที่เพิ่ม</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($materials as $material): ?>
                                    <tr>
                                        <td class="text-center"><?= $material['display_order'] ?></td>
                                        <td class="font-mono text-sm"><?= htmlspecialchars($material['material_code']) ?></td>
                                        <td class="font-semibold"><?= htmlspecialchars($material['material_name']) ?></td>
                                        <td><?= htmlspecialchars($material['unit']) ?></td>
                                        <td class="text-center">
                                            <?php if ($material['record_count'] > 0): ?>
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">
                                                    <?= $material['record_count'] ?> ครั้ง
                                                </span>
                                            <?php else: ?>
                                                <span class="text-gray-500">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($material['last_record_date']): ?>
                                                <?= date('d/m/Y', strtotime($material['last_record_date'])) ?>
                                            <?php else: ?>
                                                <span class="text-gray-500">ยังไม่เคยบันทึก</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($material['created_at'])) ?></td>
                                        <td>
                                            <a href="?edit=<?= $material['id'] ?>" 
                                               class="btn-small btn-edit">✏️ แก้ไข</a>
                                            
                                            <?php if ($material['record_count'] == 0): ?>
                                                <button onclick="deleteMaterial(<?= $material['id'] ?>, '<?= htmlspecialchars($material['material_name']) ?>')" 
                                                        class="btn-small btn-delete">🗑️ ลบ</button>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-500">มีข้อมูลแล้ว</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <script>
        function deleteMaterial(id, name) {
            if (confirm(`คุณต้องการลบวัตถุดิบ "${name}" หรือไม่?\n\n⚠️ หากวัตถุดิบนี้มีข้อมูลการบันทึกแล้ว จะไม่สามารถลบได้`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>
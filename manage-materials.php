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
                    $subUnit = isset($_POST['sub_unit']) ? trim($_POST['sub_unit']) ?: null : null;
                    $displayOrder = (int)$_POST['display_order'];
                    
                    if (empty($materialCode) || empty($materialName) || empty($unit)) {
                        throw new Exception('กรุณากรอกข้อมูลให้ครบ');
                    }
                    
                    // Check if sub_unit column exists
                    try {
                        $db->query("SELECT sub_unit FROM raw_materials LIMIT 1");
                        $stmt = $db->prepare("INSERT INTO raw_materials (material_code, material_name, unit, sub_unit, display_order) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$materialCode, $materialName, $unit, $subUnit, $displayOrder]);
                    } catch (Exception $e) {
                        // sub_unit column doesn't exist, use basic insert
                        $stmt = $db->prepare("INSERT INTO raw_materials (material_code, material_name, unit, display_order) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$materialCode, $materialName, $unit, $displayOrder]);
                    }
                    $success = "เพิ่มวัตถุดิบสำเร็จ";
                    break;
                    
                case 'edit':
                    $id = (int)$_POST['id'];
                    $materialCode = trim($_POST['material_code']);
                    $materialName = trim($_POST['material_name']);
                    $unit = trim($_POST['unit']);
                    $subUnit = isset($_POST['sub_unit']) ? trim($_POST['sub_unit']) ?: null : null;
                    $displayOrder = (int)$_POST['display_order'];
                    
                    if (empty($materialCode) || empty($materialName) || empty($unit)) {
                        throw new Exception('กรุณากรอกข้อมูลให้ครบ');
                    }
                    
                    // Check if sub_unit column exists
                    try {
                        $db->query("SELECT sub_unit FROM raw_materials LIMIT 1");
                        $stmt = $db->prepare("UPDATE raw_materials SET material_code = ?, material_name = ?, unit = ?, sub_unit = ?, display_order = ? WHERE id = ?");
                        $stmt->execute([$materialCode, $materialName, $unit, $subUnit, $displayOrder, $id]);
                    } catch (Exception $e) {
                        // sub_unit column doesn't exist, use basic update
                        $stmt = $db->prepare("UPDATE raw_materials SET material_code = ?, material_name = ?, unit = ?, display_order = ? WHERE id = ?");
                        $stmt->execute([$materialCode, $materialName, $unit, $displayOrder, $id]);
                    }
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
                    
                case 'quick_edit':
                    $id = (int)$_POST['id'];
                    $field = $_POST['field'];
                    $value = trim($_POST['value']);
                    
                    // Validate field name for security
                    $allowedFields = ['material_code', 'material_name', 'unit', 'sub_unit', 'display_order'];
                    if (!in_array($field, $allowedFields)) {
                        throw new Exception('ฟิลด์ไม่ถูกต้อง');
                    }
                    
                    if (empty($value) && $field !== 'sub_unit') {
                        throw new Exception('กรุณากรอกข้อมูล');
                    }
                    
                    // Special handling for display_order
                    if ($field === 'display_order') {
                        $value = (int)$value;
                        if ($value < 1) {
                            throw new Exception('ลำดับแสดงต้องมากกว่า 0');
                        }
                    }
                    
                    // Check if we can update this field
                    if ($field === 'sub_unit' && !$hasSubUnit) {
                        throw new Exception('ระบบยังไม่รองรับหน่วยย่อย กรุณาอัพเดทฐานข้อมูลก่อน');
                    }
                    
                    // Update the field
                    $stmt = $db->prepare("UPDATE raw_materials SET {$field} = ? WHERE id = ?");
                    $stmt->execute([$value, $id]);
                    
                    $fieldNames = [
                        'material_code' => 'รหัสวัตถุดิบ',
                        'material_name' => 'ชื่อวัตถุดิบ',
                        'unit' => 'หน่วย',
                        'sub_unit' => 'หน่วยย่อย',
                        'display_order' => 'ลำดับแสดง'
                    ];
                    
                    $success = "แก้ไข{$fieldNames[$field]}สำเร็จ";
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
    
    // Check if sub_unit column exists
    $hasSubUnit = false;
    try {
        $db->query("SELECT sub_unit FROM raw_materials LIMIT 1");
        $hasSubUnit = true;
    } catch (Exception $e) {
        // sub_unit column doesn't exist
    }
    
    if ($hasSubUnit) {
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
    } else {
        $stmt = $db->query("
            SELECT 
                rm.*,
                '' as sub_unit,
                COUNT(dsr.id) as record_count,
                MAX(dsr.record_date) as last_record_date
            FROM raw_materials rm
            LEFT JOIN daily_stock_records dsr ON rm.id = dsr.material_id
            GROUP BY rm.id
            ORDER BY rm.display_order ASC, rm.material_name ASC
        ");
    }
    $materials = $stmt->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
    $materials = [];
    $hasSubUnit = false;
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
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: all 0.2s ease;
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
        .edit-inline {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .edit-inline button {
            padding: 0.125rem 0.25rem;
            font-size: 0.75rem;
            min-width: auto;
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
        .grid-cols-5 {
            grid-template-columns: repeat(5, 1fr);
        }
        @media (max-width: 768px) {
            .grid-cols-2, .grid-cols-4, .grid-cols-5 {
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
                        <a href="/add-stock.php" class="text-orange-600 hover:text-orange-800 text-sm">📦 เพิ่มสต็อกเข้า</a>
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
                    
                    <div class="grid <?= $hasSubUnit ? 'grid-cols-5' : 'grid-cols-4' ?> gap-4">
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
                            <label for="unit"><?= $hasSubUnit ? 'หน่วยหลัก' : 'หน่วย' ?></label>
                            <input type="text" 
                                   id="unit" 
                                   name="unit" 
                                   class="form-input" 
                                   value="<?= htmlspecialchars($editMaterial['unit'] ?? '') ?>"
                                   placeholder="<?= $hasSubUnit ? 'เช่น ถุง, กล่อง, ขวด' : 'เช่น ลิตร, กิโลกรัม' ?>"
                                   required>
                        </div>
                        
                        <?php if ($hasSubUnit): ?>
                        <div class="form-group">
                            <label for="sub_unit">หน่วยย่อย (ไม่บังคับ)</label>
                            <input type="text" 
                                   id="sub_unit" 
                                   name="sub_unit" 
                                   class="form-input" 
                                   value="<?= htmlspecialchars($editMaterial['sub_unit'] ?? '') ?>"
                                   placeholder="เช่น ลิตร, กิโลกรัม">
                        </div>
                        <?php endif; ?>
                        
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
                
                <div class="alert alert-info mb-4">
                    <p><strong>💡 วิธีแก้ไข:</strong> คลิกปุ่ม <span class="bg-blue-500 text-white px-2 py-1 rounded text-xs">✏️</span> ข้างข้อมูลที่ต้องการแก้ไข</p>
                </div>
                
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
                                    <th><?= $hasSubUnit ? 'หน่วยหลัก' : 'หน่วย' ?></th>
                                    <?php if ($hasSubUnit): ?>
                                    <th>หน่วยย่อย</th>
                                    <?php endif; ?>
                                    <th>จำนวนการบันทึก</th>
                                    <th>บันทึกล่าสุด</th>
                                    <th>วันที่เพิ่ม</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($materials as $material): ?>
                                    <tr>
                                        <td class="text-center">
                                            <div class="edit-inline justify-center">
                                                <button onclick="editField(<?= $material['id'] ?>, 'display_order', '<?= $material['display_order'] ?>', 'ลำดับแสดง', 'number')" 
                                                        class="btn-small btn-edit" title="แก้ไขลำดับ">✏️</button>
                                                <?= $material['display_order'] ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="edit-inline">
                                                <button onclick="editField(<?= $material['id'] ?>, 'material_code', '<?= htmlspecialchars($material['material_code']) ?>', 'รหัสวัตถุดิบ')" 
                                                        class="btn-small btn-edit" title="แก้ไขรหัส">✏️</button>
                                                <span class="font-mono text-sm"><?= htmlspecialchars($material['material_code']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="edit-inline">
                                                <button onclick="editField(<?= $material['id'] ?>, 'material_name', '<?= htmlspecialchars($material['material_name']) ?>', 'ชื่อวัตถุดิบ')" 
                                                        class="btn-small btn-edit" title="แก้ไขชื่อ">✏️</button>
                                                <span class="font-semibold"><?= htmlspecialchars($material['material_name']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="edit-inline">
                                                <button onclick="editField(<?= $material['id'] ?>, 'unit', '<?= htmlspecialchars($material['unit']) ?>', '<?= $hasSubUnit ? 'หน่วยหลัก' : 'หน่วย' ?>')" 
                                                        class="btn-small btn-edit" title="แก้ไขหน่วย">✏️</button>
                                                <?= htmlspecialchars($material['unit']) ?>
                                            </div>
                                        </td>
                                        <?php if ($hasSubUnit): ?>
                                        <td>
                                            <div class="edit-inline">
                                                <button onclick="editField(<?= $material['id'] ?>, 'sub_unit', '<?= htmlspecialchars($material['sub_unit'] ?? '') ?>', 'หน่วยย่อย')" 
                                                        class="btn-small btn-edit" title="แก้ไขหน่วยย่อย">✏️</button>
                                                <?php if (!empty($material['sub_unit'])): ?>
                                                    <span class="text-gray-600"><?= htmlspecialchars($material['sub_unit']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-gray-400">-</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <?php endif; ?>
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
                                                <button onclick="editMaterialInline(<?= $material['id'] ?>, '<?= htmlspecialchars($material['material_name']) ?>', '<?= htmlspecialchars($material['unit']) ?>', '<?= htmlspecialchars($material['sub_unit']) ?>')" 
                                                        class="btn-small" style="background: #f59e0b; color: white;">📝 แก้ไขด่วน</button>
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
        
        function editField(id, field, currentValue, fieldLabel, inputType = 'text') {
            let newValue;
            
            if (inputType === 'number') {
                newValue = prompt(`แก้ไข${fieldLabel}:`, currentValue);
                if (newValue !== null) {
                    newValue = parseInt(newValue);
                    if (isNaN(newValue) || newValue < 1) {
                        alert('กรุณากรอกตัวเลขที่ถูกต้อง (มากกว่า 0)');
                        return;
                    }
                }
            } else {
                newValue = prompt(`แก้ไข${fieldLabel}:`, currentValue);
            }
            
            if (newValue !== null && newValue.toString().trim() !== '' && newValue.toString() !== currentValue.toString()) {
                // Create a form to submit the change
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="quick_edit">
                    <input type="hidden" name="id" value="${id}">
                    <input type="hidden" name="field" value="${field}">
                    <input type="hidden" name="value" value="${newValue.toString().trim()}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Legacy function for backward compatibility
        function editMaterialInline(id, currentName, currentUnit, currentSubUnit) {
            editField(id, 'material_name', currentName, 'ชื่อวัตถุดิบ');
        }
    </script>
</body>
</html>
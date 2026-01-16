<?php
require_once 'config.php';
require_once 'auth.php';

// Require admin access
requireAdmin();

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
                    $unitQuantity = isset($_POST['unit_quantity']) ? (float)$_POST['unit_quantity'] : 0.00;
                    $subUnitQuantity = isset($_POST['sub_unit_quantity']) ? (float)$_POST['sub_unit_quantity'] : 0.00;
                    $displayOrder = (int)$_POST['display_order'];
                    
                    if (empty($materialCode) || empty($materialName) || empty($unit)) {
                        throw new Exception('กรุณากรอกข้อมูลให้ครบ');
                    }
                    
                    // Check if quantity columns exist
                    try {
                        $db->query("SELECT unit_quantity, sub_unit_quantity FROM raw_materials LIMIT 1");
                        // Both quantity columns exist
                        try {
                            $db->query("SELECT sub_unit FROM raw_materials LIMIT 1");
                            $stmt = $db->prepare("INSERT INTO raw_materials (material_code, material_name, unit, sub_unit, unit_quantity, sub_unit_quantity, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$materialCode, $materialName, $unit, $subUnit, $unitQuantity, $subUnitQuantity, $displayOrder]);
                        } catch (Exception $e) {
                            // sub_unit column doesn't exist, use basic insert with quantities
                            $stmt = $db->prepare("INSERT INTO raw_materials (material_code, material_name, unit, unit_quantity, sub_unit_quantity, display_order) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$materialCode, $materialName, $unit, $unitQuantity, $subUnitQuantity, $displayOrder]);
                        }
                    } catch (Exception $e) {
                        // Quantity columns don't exist, check for sub_unit
                        try {
                            $db->query("SELECT sub_unit FROM raw_materials LIMIT 1");
                            $stmt = $db->prepare("INSERT INTO raw_materials (material_code, material_name, unit, sub_unit, display_order) VALUES (?, ?, ?, ?, ?)");
                            $stmt->execute([$materialCode, $materialName, $unit, $subUnit, $displayOrder]);
                        } catch (Exception $e) {
                            // Only basic columns exist
                            $stmt = $db->prepare("INSERT INTO raw_materials (material_code, material_name, unit, display_order) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$materialCode, $materialName, $unit, $displayOrder]);
                        }
                    }
                    $success = "เพิ่มวัตถุดิบสำเร็จ";
                    break;
                    
                case 'edit':
                    $id = (int)$_POST['id'];
                    $materialCode = trim($_POST['material_code']);
                    $materialName = trim($_POST['material_name']);
                    $unit = trim($_POST['unit']);
                    $subUnit = isset($_POST['sub_unit']) ? trim($_POST['sub_unit']) ?: null : null;
                    $unitQuantity = isset($_POST['unit_quantity']) ? (float)$_POST['unit_quantity'] : 0.00;
                    $subUnitQuantity = isset($_POST['sub_unit_quantity']) ? (float)$_POST['sub_unit_quantity'] : 0.00;
                    $displayOrder = (int)$_POST['display_order'];
                    
                    if (empty($materialCode) || empty($materialName) || empty($unit)) {
                        throw new Exception('กรุณากรอกข้อมูลให้ครบ');
                    }
                    
                    // Check if quantity columns exist
                    try {
                        $db->query("SELECT unit_quantity, sub_unit_quantity FROM raw_materials LIMIT 1");
                        // Both quantity columns exist
                        try {
                            $db->query("SELECT sub_unit FROM raw_materials LIMIT 1");
                            $stmt = $db->prepare("UPDATE raw_materials SET material_code = ?, material_name = ?, unit = ?, sub_unit = ?, unit_quantity = ?, sub_unit_quantity = ?, display_order = ? WHERE id = ?");
                            $stmt->execute([$materialCode, $materialName, $unit, $subUnit, $unitQuantity, $subUnitQuantity, $displayOrder, $id]);
                        } catch (Exception $e) {
                            // sub_unit column doesn't exist, use basic update with quantities
                            $stmt = $db->prepare("UPDATE raw_materials SET material_code = ?, material_name = ?, unit = ?, unit_quantity = ?, sub_unit_quantity = ?, display_order = ? WHERE id = ?");
                            $stmt->execute([$materialCode, $materialName, $unit, $unitQuantity, $subUnitQuantity, $displayOrder, $id]);
                        }
                    } catch (Exception $e) {
                        // Quantity columns don't exist, check for sub_unit
                        try {
                            $db->query("SELECT sub_unit FROM raw_materials LIMIT 1");
                            $stmt = $db->prepare("UPDATE raw_materials SET material_code = ?, material_name = ?, unit = ?, sub_unit = ?, display_order = ? WHERE id = ?");
                            $stmt->execute([$materialCode, $materialName, $unit, $subUnit, $displayOrder, $id]);
                        } catch (Exception $e) {
                            // Only basic columns exist
                            $stmt = $db->prepare("UPDATE raw_materials SET material_code = ?, material_name = ?, unit = ?, display_order = ? WHERE id = ?");
                            $stmt->execute([$materialCode, $materialName, $unit, $displayOrder, $id]);
                        }
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
                    $allowedFields = ['material_code', 'material_name', 'unit', 'sub_unit', 'unit_quantity', 'sub_unit_quantity', 'display_order'];
                    if (!in_array($field, $allowedFields)) {
                        throw new Exception('ฟิลด์ไม่ถูกต้อง');
                    }
                    
                    if (empty($value) && !in_array($field, ['sub_unit', 'unit_quantity', 'sub_unit_quantity'])) {
                        throw new Exception('กรุณากรอกข้อมูล');
                    }
                    
                    // Special handling for numeric fields
                    if (in_array($field, ['display_order', 'unit_quantity', 'sub_unit_quantity'])) {
                        $value = (float)$value;
                        if ($field === 'display_order' && $value < 1) {
                            throw new Exception('ลำดับแสดงต้องมากกว่า 0');
                        }
                        if (in_array($field, ['unit_quantity', 'sub_unit_quantity']) && $value < 0) {
                            throw new Exception('จำนวนต้องไม่ติดลบ');
                        }
                    }
                    
                    // Check if we can update this field
                    if ($field === 'sub_unit' && !$dbHasSubUnit) {
                        throw new Exception('ระบบยังไม่รองรับหน่วยย่อย กรุณาอัพเดทฐานข้อมูลก่อน');
                    }
                    
                    if (in_array($field, ['unit_quantity', 'sub_unit_quantity']) && !$dbHasQuantities) {
                        throw new Exception('ระบบยังไม่รองรับจำนวนคู่ กรุณาอัพเดทฐานข้อมูลก่อน');
                    }
                    
                    // Update the field
                    $stmt = $db->prepare("UPDATE raw_materials SET {$field} = ? WHERE id = ?");
                    $stmt->execute([$value, $id]);
                    
                    $fieldNames = [
                        'material_code' => 'รหัสวัตถุดิบ',
                        'material_name' => 'ชื่อวัตถุดิบ',
                        'unit' => 'หน่วย',
                        'sub_unit' => 'หน่วยย่อย',
                        'unit_quantity' => 'จำนวนหน่วยหลัก',
                        'sub_unit_quantity' => 'จำนวนหน่วยย่อย',
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
    
    // Always show sub_unit and quantity fields (will be created if needed)
    $hasSubUnit = true;
    $hasQuantities = true;
    
    // Check if columns actually exist in database
    $dbHasSubUnit = false;
    $dbHasQuantities = false;
    try {
        $db->query("SELECT sub_unit FROM raw_materials LIMIT 1");
        $dbHasSubUnit = true;
        
        // Check if quantity columns exist
        $db->query("SELECT unit_quantity, sub_unit_quantity FROM raw_materials LIMIT 1");
        $dbHasQuantities = true;
    } catch (Exception $e) {
        // columns don't exist yet
    }
    
    if ($dbHasSubUnit && $dbHasQuantities) {
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
    } else if ($dbHasSubUnit) {
        $stmt = $db->query("
            SELECT 
                rm.*,
                0.00 as unit_quantity,
                0.00 as sub_unit_quantity,
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
                0.00 as unit_quantity,
                0.00 as sub_unit_quantity,
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
    $hasSubUnit = true;  // Always show UI
    $hasQuantities = true;  // Always show UI
    $dbHasSubUnit = false;
    $dbHasQuantities = false;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#C4161C">
    <title>จัดการวัตถุดิบ - Hazel</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Responsive table - Force smaller sizes */
        .material-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 0.75rem; /* Smaller by default */
            table-layout: fixed; /* Force column widths */
        }
        
        .material-table th,
        .material-table td {
            border: 1px solid #d1d5db;
            padding: 0.375rem; /* Smaller padding */
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            overflow: hidden;
        }
        
        /* Column widths - Updated for separate unit columns and quantities */
        .material-table th:nth-child(1), .material-table td:nth-child(1) { width: 6%; } /* ลำดับ */
        .material-table th:nth-child(2), .material-table td:nth-child(2) { width: 10%; } /* รหัส */
        .material-table th:nth-child(3), .material-table td:nth-child(3) { width: 20%; } /* ชื่อ */
        .material-table th:nth-child(4), .material-table td:nth-child(4) { width: 8%; } /* หน่วยหลัก */
        .material-table th:nth-child(5), .material-table td:nth-child(5) { width: 8%; } /* หน่วยย่อย */
        .material-table th:nth-child(6), .material-table td:nth-child(6) { width: 12%; } /* จำนวนคงเหลือ */
        
        .material-table th {
            background: #f9fafb;
            font-weight: 600;
            font-size: 0.625rem; /* Even smaller headers */
        }
        
        .material-table tr:hover {
            background: #f9fafb;
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .material-table {
                font-size: 0.625rem; /* Very small on mobile */
            }
            .material-table th,
            .material-table td {
                padding: 0.25rem;
            }
            .edit-inline {
                flex-direction: column;
                gap: 0.125rem;
            }
            .edit-inline button {
                font-size: 0.5rem;
                padding: 0.125rem 0.25rem;
                min-width: 20px;
            }
            .edit-inline span {
                font-size: 0.625rem;
            }
        }
        
        /* Force hide columns */
        .force-hide {
            display: none !important;
        }
        
        /* Compact mode */
        .table-compact .material-table {
            font-size: 0.625rem;
        }
        .table-compact .material-table th,
        .table-compact .material-table td {
            padding: 0.125rem;
        }
        .btn-small {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            margin: 0 0.25rem;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-block;
            text-decoration: none;
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
        .grid-cols-7 {
            grid-template-columns: repeat(7, 1fr);
        }
        @media (max-width: 768px) {
            .grid-cols-2, .grid-cols-3, .grid-cols-4, .grid-cols-5, .grid-cols-7 {
                grid-template-columns: 1fr;
            }
        }
        
        /* Form sections styling */
        .form-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .form-section h4 {
            color: #374151;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .form-group small {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.75rem;
            color: #6b7280;
        }
        
        .quantity-preview {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 1px solid #93c5fd;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-top: 0.75rem;
        }
        
        .flex {
            display: flex;
        }
        
        .items-center {
            align-items: center;
        }
        
        .space-x-2 > * + * {
            margin-left: 0.5rem;
        }
        
        .space-x-3 > * + * {
            margin-left: 0.75rem;
        }
        
        .flex-1 {
            flex: 1 1 0%;
        }
        
        .min-w-0 {
            min-width: 0;
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
                        <a href="/migrate.php" class="text-purple-600 hover:text-purple-800 text-sm">⚡ อัพเดทระบบ</a>
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

            <!-- Database Update Notice -->
            <?php if (!$dbHasSubUnit || !$dbHasQuantities): ?>
                <div class="material-card mb-6" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div style="font-size: 2rem;">⚡</div>
                            <div>
                                <h4 class="text-lg font-semibold text-amber-800">อัพเดทระบบเพื่อใช้งานฟีเจอร์ใหม่</h4>
                                <p class="text-amber-700 text-sm mt-1">
                                    ระบบต้องการอัพเดทฐานข้อมูลเพื่อรองรับ <strong>หน่วยย่อย</strong> และ <strong>จำนวนคู่</strong>
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="/migrate.php" class="btn-primary" style="background: #f59e0b; border-color: #f59e0b;">
                                🚀 อัพเดทเลย
                            </a>
                        </div>
                    </div>
                </div>
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
                    
                    <!-- Basic Info Section -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium mb-3 text-gray-700">📝 ข้อมูลพื้นฐาน</h4>
                        <div class="grid grid-cols-3 gap-4">
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
                    </div>
                    
                    <!-- Units Section -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium mb-3 text-gray-700">📏 หน่วยการวัด</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label for="unit">หน่วยหลัก</label>
                                <input type="text" 
                                       id="unit" 
                                       name="unit" 
                                       class="form-input" 
                                       value="<?= htmlspecialchars($editMaterial['unit'] ?? '') ?>"
                                       placeholder="เช่น ถุง, กล่อง, ขวด"
                                       required>
                                <small class="text-gray-500">หน่วยบรรจุภัณฑ์หลัก</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="sub_unit">หน่วยย่อย</label>
                                <input type="text" 
                                       id="sub_unit" 
                                       name="sub_unit" 
                                       class="form-input" 
                                       value="<?= htmlspecialchars($editMaterial['sub_unit'] ?? '') ?>"
                                       placeholder="เช่น ลิตร, กิโลกรัม">
                                <small class="text-gray-500">หน่วยน้ำหนัก/ปริมาตร</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quantities Section -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium mb-3 text-gray-700">🔢 จำนวนคงเหลือ</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label for="unit_quantity">จำนวนหน่วยหลัก</label>
                                <div class="flex items-center space-x-2">
                                    <input type="number" 
                                           id="unit_quantity" 
                                           name="unit_quantity" 
                                           class="form-input flex-1" 
                                           value="<?= htmlspecialchars($editMaterial['unit_quantity'] ?? '0') ?>"
                                           step="0.01"
                                           min="0"
                                           placeholder="0">
                                    <span class="text-gray-500 text-sm min-w-0" id="unit_display">หน่วย</span>
                                </div>
                                <small class="text-gray-500">เช่น 2 ถุง</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="sub_unit_quantity">จำนวนหน่วยย่อย</label>
                                <div class="flex items-center space-x-2">
                                    <input type="number" 
                                           id="sub_unit_quantity" 
                                           name="sub_unit_quantity" 
                                           class="form-input flex-1" 
                                           value="<?= htmlspecialchars($editMaterial['sub_unit_quantity'] ?? '0') ?>"
                                           step="0.01"
                                           min="0"
                                           placeholder="0">
                                    <span class="text-gray-500 text-sm min-w-0" id="sub_unit_display">หน่วย</span>
                                </div>
                                <small class="text-gray-500">เช่น 1.5 ลิตร</small>
                            </div>
                        </div>
                        
                        <!-- Preview -->
                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="text-sm text-blue-700">
                                <strong>🔍 ตัวอย่างการแสดงผล:</strong> 
                                <span id="quantity_preview" class="font-medium">ยังไม่ระบุจำนวน</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <button type="submit" class="btn-primary">
                            <?= $editMaterial ? '💾 บันทึกการแก้ไข' : '➕ เพิ่มวัตถุดิบ' ?>
                        </button>
                        <?php if ($editMaterial): ?>
                            <a href="/manage-materials.php" class="btn-secondary">❌ ยกเลิก</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Material List -->
            <div class="material-card">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">🧪 รายการวัตถุดิบ (<?= count($materials) ?> รายการ)</h3>
                    <div class="flex gap-2">
                        <button onclick="toggleCompactMode()" id="compactBtn" class="btn-small" style="background: #6b7280; color: white;">
                            📱 กะทัดรัด
                        </button>
                        <button onclick="toggleMobileColumns()" id="columnsBtn" class="btn-small" style="background: #8b5cf6; color: white;">
                            👁️ ซ่อน/แสดง
                        </button>
                        <button onclick="forceMinimal()" id="minimalBtn" class="btn-small" style="background: #ef4444; color: white;">
                            🔥 ขั้นต่ำ
                        </button>
                    </div>
                </div>
                
                <div class="alert alert-info mb-4">
                    <p><strong>💡 วิธีใช้:</strong></p>
                    <ul class="list-disc list-inside text-sm mt-2 space-y-1">
                        <li>คลิกปุ่ม <span class="bg-blue-500 text-white px-2 py-1 rounded text-xs">✏️ แก้ไข</span> ในคอลัมน์จัดการเพื่อแก้ไขข้อมูล</li>
                        <li>คลิก <span class="bg-gray-500 text-white px-2 py-1 rounded text-xs">📱 กะทัดรัด</span> เพื่อลดขนาดตาราง</li>
                        <li>คลิก <span class="bg-purple-500 text-white px-2 py-1 rounded text-xs">👁️ ซ่อน/แสดง</span> เพื่อจัดการคอลัมน์</li>
                        <li>คลิก <span class="bg-red-500 text-white px-2 py-1 rounded text-xs">🔥 ขั้นต่ำ</span> เพื่อแสดงเฉพาะข้อมูลสำคัญ</li>
                        <li><strong>หน่วย:</strong> แยกเป็น 2 คอลัมน์ - หน่วยหลัก (เช่น ถุง) และหน่วยย่อย (เช่น ลิตร)</li>
                        <li><strong>จำนวนคงเหลือ:</strong> ระบุจำนวนในรูปแบบ "2 ถุง 1 ลิตร" เพื่อแสดงสต็อกปัจจุบัน</li>
                        <?php if (!$dbHasSubUnit || !$dbHasQuantities): ?>
                        <li><strong>⚡ สำคัญ:</strong> คลิก <span class="bg-amber-500 text-white px-2 py-1 rounded text-xs">🚀 อัพเดทเลย</span> เพื่อเปิดใช้ฟีเจอร์ใหม่</li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <?php if (empty($materials)): ?>
                    <div class="text-center py-8">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🧪</div>
                        <p class="text-gray-600">ยังไม่มีวัตถุดิบในระบบ</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto" id="tableContainer">
                        <table class="material-table" id="materialsTable">
                            <thead>
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>รหัส</th>
                                    <th>ชื่อวัตถุดิบ</th>
                                    <th>หน่วยหลัก</th>
                                    <th class="hide-mobile">หน่วยย่อย</th>
                                    <th class="hide-mobile">จำนวนคงเหลือ</th>
                                    <th class="hide-mobile">จำนวนการบันทึก</th>
                                    <th class="hide-mobile">บันทึกล่าสุด</th>
                                    <th class="hide-mobile">วันที่เพิ่ม</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($materials as $material): ?>
                                    <tr>
                                        <td class="text-center"><?= $material['display_order'] ?></td>
                                        <td class="font-mono text-sm"><?= htmlspecialchars($material['material_code']) ?></td>
                                        <td class="font-semibold"><?= htmlspecialchars($material['material_name']) ?></td>
                                        <td class="font-medium"><?= htmlspecialchars($material['unit']) ?></td>
                                        <td class="hide-mobile">
                                            <?php if (!empty($material['sub_unit'])): ?>
                                                <span class="text-gray-700"><?= htmlspecialchars($material['sub_unit']) ?></span>
                                            <?php else: ?>
                                                <span class="text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="hide-mobile">
                                            <?php 
                                            $displayQuantity = '';
                                            if (isset($material['unit_quantity']) && $material['unit_quantity'] > 0) {
                                                $displayQuantity .= number_format($material['unit_quantity'], 2) . ' ' . $material['unit'];
                                            }
                                            if (isset($material['sub_unit_quantity']) && $material['sub_unit_quantity'] > 0 && !empty($material['sub_unit'])) {
                                                if ($displayQuantity) $displayQuantity .= ' ';
                                                $displayQuantity .= number_format($material['sub_unit_quantity'], 2) . ' ' . $material['sub_unit'];
                                            }
                                            if (empty($displayQuantity)) {
                                                $displayQuantity = '<span class="text-gray-500">ยังไม่ระบุ</span>';
                                            }
                                            echo $displayQuantity;
                                            ?>
                                        </td>
                                        <td class="text-center hide-mobile">
                                            <?php if ($material['record_count'] > 0): ?>
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">
                                                    <?= $material['record_count'] ?> ครั้ง
                                                </span>
                                            <?php else: ?>
                                                <span class="text-gray-500">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="hide-mobile">
                                            <?php if ($material['last_record_date']): ?>
                                                <?= date('d/m/Y', strtotime($material['last_record_date'])) ?>
                                            <?php else: ?>
                                                <span class="text-gray-500">ยังไม่เคยบันทึก</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="hide-mobile"><?= date('d/m/Y H:i', strtotime($material['created_at'])) ?></td>
                                        <td>
                                            <a href="?edit=<?= $material['id'] ?>" 
                                               class="btn-small btn-edit">✏️ แก้ไข</a>
                                            
                                            <?php if ($material['record_count'] == 0): ?>
                                                <button onclick="deleteMaterial(<?= $material['id'] ?>, '<?= htmlspecialchars($material['material_name']) ?>')" 
                                                        class="btn-small btn-delete">🗑️ ลบ</button>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-500 block mt-1">มีข้อมูลแล้ว</span>
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
        
        // Update unit displays and preview in real-time
        function updatePreview() {
            const unit = document.getElementById('unit').value || 'หน่วย';
            const subUnit = document.getElementById('sub_unit').value || 'หน่วย';
            const unitQty = parseFloat(document.getElementById('unit_quantity').value) || 0;
            const subUnitQty = parseFloat(document.getElementById('sub_unit_quantity').value) || 0;
            
            // Update unit labels
            const unitDisplay = document.getElementById('unit_display');
            const subUnitDisplay = document.getElementById('sub_unit_display');
            if (unitDisplay) unitDisplay.textContent = unit;
            if (subUnitDisplay) subUnitDisplay.textContent = subUnit;
            
            // Update preview
            let preview = '';
            if (unitQty > 0) {
                preview += `${unitQty} ${unit}`;
            }
            if (subUnitQty > 0) {
                if (preview) preview += ' ';
                preview += `${subUnitQty} ${subUnit}`;
            }
            
            if (!preview) {
                preview = 'ยังไม่ระบุจำนวน';
            }
            
            const previewElement = document.getElementById('quantity_preview');
            if (previewElement) previewElement.textContent = preview;
        }
        
        // Add event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = ['unit', 'sub_unit', 'unit_quantity', 'sub_unit_quantity'];
            inputs.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('input', updatePreview);
                }
            });
            
            // Initial update
            updatePreview();
        });
        
        // Toggle compact mode
        function toggleCompactMode() {
            const container = document.getElementById('tableContainer');
            const btn = document.getElementById('compactBtn');
            
            if (container && container.classList.contains('table-compact')) {
                container.classList.remove('table-compact');
                btn.textContent = '📱 กะทัดรัด';
                btn.style.background = '#6b7280';
            } else if (container) {
                container.classList.add('table-compact');
                btn.textContent = '📱 ปกติ';
                btn.style.background = '#059669';
            }
        }
        
        // Toggle mobile columns - Force hide with !important
        function toggleMobileColumns() {
            const hiddenCols = document.querySelectorAll('.hide-mobile');
            const btn = document.getElementById('columnsBtn');
            
            let isHidden = false;
            hiddenCols.forEach(col => {
                if (col.classList.contains('force-hide')) {
                    isHidden = true;
                }
            });
            
            if (isHidden) {
                // Show columns
                hiddenCols.forEach(col => {
                    col.classList.remove('force-hide');
                });
                btn.textContent = '👁️ ซ่อน';
                btn.style.background = '#8b5cf6';
            } else {
                // Hide columns
                hiddenCols.forEach(col => {
                    col.classList.add('force-hide');
                });
                btn.textContent = '👁️ แสดง';
                btn.style.background = '#059669';
            }
        }
        
        // Force minimal mode - show only essential columns
        function forceMinimal() {
            // Hide: รหัส, จำนวนการบันทึก, บันทึกล่าสุด, วันที่เพิ่ม
            // Keep: ลำดับ, ชื่อ, หน่วยหลัก, หน่วยย่อย, จัดการ
            const hideColumns = document.querySelectorAll('th:nth-child(2), td:nth-child(2), .hide-mobile'); 
            const btn = document.getElementById('minimalBtn');
            
            let isMinimal = btn.textContent.includes('ปกติ');
            
            if (isMinimal) {
                // Show all
                hideColumns.forEach(col => {
                    col.classList.remove('force-hide');
                });
                btn.textContent = '🔥 ขั้นต่ำ';
                btn.style.background = '#ef4444';
            } else {
                // Hide selected columns
                hideColumns.forEach(col => {
                    col.classList.add('force-hide');
                });
                btn.textContent = '🔥 ปกติ';
                btn.style.background = '#059669';
            }
        }
        
        // Auto-hide columns on small screens
        function checkScreenSize() {
            const hiddenCols = document.querySelectorAll('.hide-mobile');
            const btn = document.getElementById('columnsBtn');
            
            if (window.innerWidth <= 768) {
                hiddenCols.forEach(col => col.classList.add('force-hide'));
                if (btn) {
                    btn.textContent = '👁️ แสดง';
                    btn.style.background = '#059669';
                }
            } else {
                hiddenCols.forEach(col => col.classList.remove('force-hide'));
                if (btn) {
                    btn.textContent = '👁️ ซ่อน';
                    btn.style.background = '#8b5cf6';
                }
            }
        }
        
        // Check on load and resize
        window.addEventListener('load', checkScreenSize);
        window.addEventListener('resize', checkScreenSize);
    </script>
</body>
</html>
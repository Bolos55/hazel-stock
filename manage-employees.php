<?php
require_once 'config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance()->getConnection();
        
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $fullName = trim($_POST['full_name']);
                    $employeeName = trim($_POST['employee_name']);
                    
                    if (empty($fullName) || empty($employeeName)) {
                        throw new Exception('กรุณากรอกข้อมูลให้ครบ');
                    }
                    
                    $stmt = $db->prepare("INSERT INTO employees (full_name, employee_name) VALUES (?, ?)");
                    $stmt->execute([$fullName, $employeeName]);
                    $success = "เพิ่มพนักงานสำเร็จ";
                    break;
                    
                case 'edit':
                    $id = (int)$_POST['id'];
                    $fullName = trim($_POST['full_name']);
                    $employeeName = trim($_POST['employee_name']);
                    
                    if (empty($fullName) || empty($employeeName)) {
                        throw new Exception('กรุณากรอกข้อมูลให้ครบ');
                    }
                    
                    $stmt = $db->prepare("UPDATE employees SET full_name = ?, employee_name = ? WHERE id = ?");
                    $stmt->execute([$fullName, $employeeName, $id]);
                    $success = "แก้ไขพนักงานสำเร็จ";
                    break;
                    
                case 'delete':
                    $id = (int)$_POST['id'];
                    
                    // Check if employee has records
                    $stmt = $db->prepare("SELECT COUNT(*) FROM daily_stock_records WHERE employee_id = ?");
                    $stmt->execute([$id]);
                    $recordCount = $stmt->fetchColumn();
                    
                    if ($recordCount > 0) {
                        throw new Exception("ไม่สามารถลบได้ เพราะพนักงานคนนี้มีข้อมูลการบันทึกสต็อกแล้ว ({$recordCount} รายการ)");
                    }
                    
                    $stmt = $db->prepare("DELETE FROM employees WHERE id = ?");
                    $stmt->execute([$id]);
                    $success = "ลบพนักงานสำเร็จ";
                    break;
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get all employees
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("
        SELECT 
            e.*,
            COUNT(dsr.id) as record_count,
            MAX(dsr.record_date) as last_record_date
        FROM employees e
        LEFT JOIN daily_stock_records dsr ON e.id = dsr.employee_id
        GROUP BY e.id
        ORDER BY e.created_at DESC
    ");
    $employees = $stmt->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
    $employees = [];
}

// Get employee for editing
$editEmployee = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($employees as $emp) {
        if ($emp['id'] == $editId) {
            $editEmployee = $emp;
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
    <title>จัดการพนักงาน - Hazel</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .employee-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .employee-table th,
        .employee-table td {
            border: 1px solid #d1d5db;
            padding: 0.75rem;
            text-align: left;
        }
        .employee-table th {
            background: #f9fafb;
            font-weight: 600;
        }
        .employee-table tr:hover {
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
    </style>
</head>
<body>
    <div class="app-container">
        <div class="app-header hazel-header">
            <img src="assets/hazel-logo.png" alt="Hazel" class="hazel-logo">
            <div class="hazel-subtitle">Beverages & Appetizers</div>
            <h1>จัดการพนักงาน</h1>
        </div>
        
        <div class="employee-section">
            <!-- Navigation -->
            <div class="material-card mb-4">
                <div class="flex justify-between items-center">
                    <a href="/" class="text-blue-600 hover:text-blue-800">← กลับหน้าหลัก</a>
                    <div class="space-x-2">
                        <a href="/view-records.php" class="text-blue-600 hover:text-blue-800 text-sm">📊 ดูข้อมูลสต็อก</a>
                        <a href="/manage-materials.php" class="text-purple-600 hover:text-purple-800 text-sm">🧪 จัดการวัตถุดิบ</a>
                        <a href="/setup.php" class="text-gray-600 hover:text-gray-800 text-sm">🛠️ Setup</a>
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

            <!-- Add/Edit Employee Form -->
            <div class="material-card mb-6">
                <h3 class="text-lg font-semibold mb-4">
                    <?= $editEmployee ? '✏️ แก้ไขพนักงาน' : '➕ เพิ่มพนักงานใหม่' ?>
                </h3>
                
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $editEmployee ? 'edit' : 'add' ?>">
                    <?php if ($editEmployee): ?>
                        <input type="hidden" name="id" value="<?= $editEmployee['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="full_name">ชื่อเต็ม</label>
                            <input type="text" 
                                   id="full_name" 
                                   name="full_name" 
                                   class="form-input" 
                                   value="<?= htmlspecialchars($editEmployee['full_name'] ?? '') ?>"
                                   placeholder="เช่น นายสมชาย ใจดี"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="employee_name">ชื่อเรียก</label>
                            <input type="text" 
                                   id="employee_name" 
                                   name="employee_name" 
                                   class="form-input" 
                                   value="<?= htmlspecialchars($editEmployee['employee_name'] ?? '') ?>"
                                   placeholder="เช่น สมชาย"
                                   required>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn-primary">
                            <?= $editEmployee ? '💾 บันทึกการแก้ไข' : '➕ เพิ่มพนักงาน' ?>
                        </button>
                        <?php if ($editEmployee): ?>
                            <a href="/manage-employees.php" class="btn-secondary ml-2">❌ ยกเลิก</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Employee List -->
            <div class="material-card">
                <h3 class="text-lg font-semibold mb-4">👥 รายชื่อพนักงาน (<?= count($employees) ?> คน)</h3>
                
                <?php if (empty($employees)): ?>
                    <div class="text-center py-8">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">👤</div>
                        <p class="text-gray-600">ยังไม่มีพนักงานในระบบ</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="employee-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>ชื่อเต็ม</th>
                                    <th>ชื่อเรียก</th>
                                    <th>จำนวนการบันทึก</th>
                                    <th>บันทึกล่าสุด</th>
                                    <th>วันที่เพิ่ม</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $employee): ?>
                                    <tr>
                                        <td><?= $employee['id'] ?></td>
                                        <td class="font-semibold"><?= htmlspecialchars($employee['full_name']) ?></td>
                                        <td><?= htmlspecialchars($employee['employee_name']) ?></td>
                                        <td class="text-center">
                                            <?php if ($employee['record_count'] > 0): ?>
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">
                                                    <?= $employee['record_count'] ?> ครั้ง
                                                </span>
                                            <?php else: ?>
                                                <span class="text-gray-500">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($employee['last_record_date']): ?>
                                                <?= date('d/m/Y', strtotime($employee['last_record_date'])) ?>
                                            <?php else: ?>
                                                <span class="text-gray-500">ยังไม่เคยบันทึก</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($employee['created_at'])) ?></td>
                                        <td>
                                            <a href="?edit=<?= $employee['id'] ?>" 
                                               class="btn-small btn-edit">✏️ แก้ไข</a>
                                            
                                            <?php if ($employee['record_count'] == 0): ?>
                                                <button onclick="deleteEmployee(<?= $employee['id'] ?>, '<?= htmlspecialchars($employee['full_name']) ?>')" 
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
        function deleteEmployee(id, name) {
            if (confirm(`คุณต้องการลบพนักงาน "${name}" หรือไม่?\n\n⚠️ หากพนักงานคนนี้มีข้อมูลการบันทึกสต็อกแล้ว จะไม่สามารถลบได้`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>
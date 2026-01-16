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
                    $firstName = trim($_POST['first_name']);
                    $lastName = trim($_POST['last_name']);
                    $fullName = $firstName . ' ' . $lastName;
                    $employeeName = trim($_POST['employee_name']);
                    $role = trim($_POST['role'] ?? 'employee');
                    $username = trim($_POST['username'] ?? '');
                    $password = $_POST['password'] ?? '';
                    
                    if (empty($firstName) || empty($lastName) || empty($employeeName)) {
                        throw new Exception('กรุณากรอกข้อมูลให้ครบ');
                    }
                    
                    // If creating admin, require username and password
                    if ($role === 'admin') {
                        if (empty($username) || empty($password)) {
                            throw new Exception('กรุณากรอก Username และ Password สำหรับแอดมิน');
                        }
                        if (strlen($password) < 6) {
                            throw new Exception('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
                        }
                        
                        // Check if username already exists
                        $stmt = $db->prepare("SELECT id FROM employees WHERE username = ?");
                        $stmt->execute([$username]);
                        if ($stmt->fetch()) {
                            throw new Exception('Username นี้มีอยู่ในระบบแล้ว');
                        }
                        
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("INSERT INTO employees (full_name, first_name, last_name, employee_name, role, username, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$fullName, $firstName, $lastName, $employeeName, $role, $username, $hashedPassword]);
                    } else {
                        $stmt = $db->prepare("INSERT INTO employees (full_name, first_name, last_name, employee_name, role) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$fullName, $firstName, $lastName, $employeeName, $role]);
                    }
                    
                    $success = $role === 'admin' ? "เพิ่มแอดมินสำเร็จ" : "เพิ่มพนักงานสำเร็จ";
                    break;
                    
                case 'edit':
                    $id = (int)$_POST['id'];
                    $firstName = trim($_POST['first_name']);
                    $lastName = trim($_POST['last_name']);
                    $fullName = $firstName . ' ' . $lastName;
                    $employeeName = trim($_POST['employee_name']);
                    $role = trim($_POST['role'] ?? 'employee');
                    $username = trim($_POST['username'] ?? '');
                    $password = $_POST['password'] ?? '';
                    
                    if (empty($firstName) || empty($lastName) || empty($employeeName)) {
                        throw new Exception('กรุณากรอกข้อมูลให้ครบ');
                    }
                    
                    // If changing to admin or updating admin
                    if ($role === 'admin') {
                        if (!empty($username)) {
                            // Check if username already exists (except current user)
                            $stmt = $db->prepare("SELECT id FROM employees WHERE username = ? AND id != ?");
                            $stmt->execute([$username, $id]);
                            if ($stmt->fetch()) {
                                throw new Exception('Username นี้มีอยู่ในระบบแล้ว');
                            }
                        }
                        
                        if (!empty($password)) {
                            if (strlen($password) < 6) {
                                throw new Exception('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
                            }
                            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $db->prepare("UPDATE employees SET full_name = ?, first_name = ?, last_name = ?, employee_name = ?, role = ?, username = ?, password = ? WHERE id = ?");
                            $stmt->execute([$fullName, $firstName, $lastName, $employeeName, $role, $username, $hashedPassword, $id]);
                        } else {
                            $stmt = $db->prepare("UPDATE employees SET full_name = ?, first_name = ?, last_name = ?, employee_name = ?, role = ?, username = ? WHERE id = ?");
                            $stmt->execute([$fullName, $firstName, $lastName, $employeeName, $role, $username, $id]);
                        }
                    } else {
                        $stmt = $db->prepare("UPDATE employees SET full_name = ?, first_name = ?, last_name = ?, employee_name = ?, role = ? WHERE id = ?");
                        $stmt->execute([$fullName, $firstName, $lastName, $employeeName, $role, $id]);
                    }
                    
                    $success = "แก้ไขข้อมูลสำเร็จ";
                    break;
                    
                case 'delete':
                    $id = (int)$_POST['id'];
                    
                    // Check if employee has records
                    $stmt = $db->prepare("SELECT COUNT(*) FROM daily_stock_records WHERE employee_id = ?");
                    $stmt->execute([$id]);
                    $recordCount = $stmt->fetchColumn();
                    
                    if ($recordCount > 0) {
                        // Has records - deactivate instead of delete
                        $stmt = $db->prepare("UPDATE employees SET is_active = 0 WHERE id = ?");
                        $stmt->execute([$id]);
                        $success = "ปิดการใช้งานพนักงานสำเร็จ (มีประวัติการบันทึก {$recordCount} รายการ)";
                    } else {
                        // No records - can delete permanently
                        $stmt = $db->prepare("DELETE FROM employees WHERE id = ?");
                        $stmt->execute([$id]);
                        $success = "ลบพนักงานสำเร็จ";
                    }
                    break;
                    
                case 'activate':
                    $id = (int)$_POST['id'];
                    $stmt = $db->prepare("UPDATE employees SET is_active = 1 WHERE id = ?");
                    $stmt->execute([$id]);
                    $success = "เปิดการใช้งานพนักงานสำเร็จ";
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
        ORDER BY e.is_active DESC, e.created_at DESC
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#C4161C">
    <title>จัดการพนักงาน - Hazel</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    
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
                        <a href="/add-stock.php" class="text-orange-600 hover:text-orange-800 text-sm">📦 เพิ่มสต็อกเข้า</a>
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
                    <?= $editEmployee ? '✏️ แก้ไขพนักงาน' : '➕ เพิ่มพนักงาน/แอดมินใหม่' ?>
                </h3>
                
                <form method="POST" id="employeeForm">
                    <input type="hidden" name="action" value="<?= $editEmployee ? 'edit' : 'add' ?>">
                    <?php if ($editEmployee): ?>
                        <input type="hidden" name="id" value="<?= $editEmployee['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="first_name">ชื่อจริง</label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   class="form-input" 
                                   value="<?= htmlspecialchars($editEmployee['first_name'] ?? '') ?>"
                                   placeholder="เช่น สมชาย"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">นามสกุล</label>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   class="form-input" 
                                   value="<?= htmlspecialchars($editEmployee['last_name'] ?? '') ?>"
                                   placeholder="เช่น ใจดี"
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
                        
                        <div class="form-group">
                            <label for="role">บทบาท</label>
                            <select id="role" 
                                    name="role" 
                                    class="form-input"
                                    onchange="toggleAdminFields()">
                                <option value="employee" <?= ($editEmployee['role'] ?? 'employee') === 'employee' ? 'selected' : '' ?>>พนักงาน</option>
                                <option value="admin" <?= ($editEmployee['role'] ?? '') === 'admin' ? 'selected' : '' ?>>แอดมิน</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Admin Fields (shown only when role is admin) -->
                    <div id="adminFields" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div class="form-group">
                                <label for="username">Username (สำหรับ Login)</label>
                                <input type="text" 
                                       id="username" 
                                       name="username" 
                                       class="form-input" 
                                       value="<?= htmlspecialchars($editEmployee['username'] ?? '') ?>"
                                       placeholder="เช่น admin@example.com">
                                <small class="text-gray-500">ใช้สำหรับเข้าสู่ระบบ</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-input" 
                                       placeholder="<?= $editEmployee ? 'เว้นว่างถ้าไม่ต้องการเปลี่ยน' : 'อย่างน้อย 6 ตัวอักษร' ?>"
                                       minlength="6">
                                <small class="text-gray-500">
                                    <?= $editEmployee ? 'เว้นว่างถ้าไม่ต้องการเปลี่ยนรหัสผ่าน' : 'อย่างน้อย 6 ตัวอักษร' ?>
                                </small>
                            </div>
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
            
            <script>
                function toggleAdminFields() {
                    const role = document.getElementById('role').value;
                    const adminFields = document.getElementById('adminFields');
                    const usernameInput = document.getElementById('username');
                    const passwordInput = document.getElementById('password');
                    
                    if (role === 'admin') {
                        adminFields.style.display = 'block';
                        usernameInput.required = <?= $editEmployee ? 'false' : 'true' ?>;
                        passwordInput.required = <?= $editEmployee ? 'false' : 'true' ?>;
                    } else {
                        adminFields.style.display = 'none';
                        usernameInput.required = false;
                        passwordInput.required = false;
                    }
                }
                
                // Initialize on page load
                toggleAdminFields();
            </script>

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
                                    <th>ลำดับ</th>
                                    <th>ชื่อจริง</th>
                                    <th>นามสกุล</th>
                                    <th>ชื่อเรียก</th>
                                    <th>บทบาท</th>
                                    <th>Username</th>
                                    <th>จำนวนการบันทึก</th>
                                    <th>บันทึกล่าสุด</th>
                                    <th>วันที่เพิ่ม</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $employee): ?>
                                    <tr class="<?= ($employee['is_active'] ?? 1) == 0 ? 'opacity-50 bg-gray-50' : '' ?>">
                                        <td><?= $employee['id'] ?></td>
                                        <td><?= htmlspecialchars($employee['first_name'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($employee['last_name'] ?? '-') ?></td>
                                        <td class="font-semibold"><?= htmlspecialchars($employee['employee_name']) ?></td>
                                        <td>
                                            <?php if (($employee['role'] ?? 'employee') === 'admin'): ?>
                                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm font-semibold">
                                                    👑 แอดมิน
                                                </span>
                                            <?php else: ?>
                                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                                    👤 พนักงาน
                                                </span>
                                            <?php endif; ?>
                                            <?php if (($employee['is_active'] ?? 1) == 0): ?>
                                                <br><span class="text-xs bg-gray-500 text-white px-2 py-1 rounded mt-1 inline-block">ปิดการใช้งาน</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-sm text-gray-600">
                                            <?= htmlspecialchars($employee['username'] ?? '-') ?>
                                        </td>
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
                                            <?php if (($employee['is_active'] ?? 1) == 1): ?>
                                                <a href="?edit=<?= $employee['id'] ?>" 
                                                   class="btn-small btn-edit">✏️ แก้ไข</a>
                                                
                                                <button onclick="deactivateEmployee(<?= $employee['id'] ?>, '<?= htmlspecialchars($employee['full_name']) ?>', <?= $employee['record_count'] ?>)" 
                                                        class="btn-small btn-delete">
                                                    <?= $employee['record_count'] > 0 ? '🔒 ปิดการใช้งาน' : '🗑️ ลบ' ?>
                                                </button>
                                            <?php else: ?>
                                                <button onclick="activateEmployee(<?= $employee['id'] ?>, '<?= htmlspecialchars($employee['full_name']) ?>')" 
                                                        class="btn-small bg-green-500 hover:bg-green-600">
                                                    ✅ เปิดการใช้งาน
                                                </button>
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
        function deactivateEmployee(id, name, recordCount) {
            let message = '';
            if (recordCount > 0) {
                message = `คุณต้องการปิดการใช้งานพนักงาน "${name}" หรือไม่?\n\n📊 พนักงานคนนี้มีประวัติการบันทึก ${recordCount} รายการ\n✅ ข้อมูลจะยังคงอยู่ แต่จะไม่สามารถใช้งานได้`;
            } else {
                message = `คุณต้องการลบพนักงาน "${name}" หรือไม่?\n\n⚠️ การลบจะไม่สามารถกู้คืนได้`;
            }
            
            if (confirm(message)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
        
        function activateEmployee(id, name) {
            if (confirm(`คุณต้องการเปิดการใช้งานพนักงาน "${name}" อีกครั้งหรือไม่?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="activate">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
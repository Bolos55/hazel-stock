<?php
require_once 'config.php';
require_once 'auth.php';

// Require admin access
requireAdmin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($currentPassword && $newPassword && $confirmPassword) {
        if ($newPassword !== $confirmPassword) {
            $error = 'รหัสผ่านใหม่ไม่ตรงกัน';
        } elseif (strlen($newPassword) < 6) {
            $error = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                
                // Verify current password
                $stmt = $db->prepare("SELECT password FROM employees WHERE id = ?");
                $stmt->execute([$_SESSION['employee_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($currentPassword, $user['password'])) {
                    // Update password
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE employees SET password = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $_SESSION['employee_id']]);
                    
                    $success = 'เปลี่ยนรหัสผ่านสำเร็จ!';
                } else {
                    $error = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
                }
            } catch (Exception $e) {
                $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            }
        }
    } else {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน - Hazel Stock Management</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .change-password-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            padding: 2rem;
        }
        .change-password-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
            padding: 2rem;
        }
        .card-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 0.5rem;
        }
        .card-subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 2rem;
            font-size: 0.875rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 0.875rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(220, 38, 38, 0.3);
        }
        .btn-back {
            width: 100%;
            background: #f3f4f6;
            color: #6b7280;
            padding: 0.75rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
            transition: all 0.2s;
            display: block;
            text-align: center;
            text-decoration: none;
        }
        .btn-back:hover {
            background: #e5e7eb;
        }
        .success-message {
            background: #d1fae5;
            border: 1px solid #6ee7b7;
            color: #065f46;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .error-message {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="change-password-container">
        <div class="change-password-card">
            <h1 class="card-title">🔐 เปลี่ยนรหัสผ่าน</h1>
            <p class="card-subtitle">แอดมิน: <?php echo htmlspecialchars(getEmployeeName()); ?></p>
            
            <?php if ($success): ?>
                <div class="success-message">
                    ✅ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="current_password" class="form-label">รหัสผ่านปัจจุบัน</label>
                    <input type="password" 
                           id="current_password" 
                           name="current_password" 
                           class="form-input" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="new_password" class="form-label">รหัสผ่านใหม่</label>
                    <input type="password" 
                           id="new_password" 
                           name="new_password" 
                           class="form-input" 
                           minlength="6"
                           required>
                    <small style="color: #6b7280; font-size: 0.75rem;">อย่างน้อย 6 ตัวอักษร</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           class="form-input" 
                           minlength="6"
                           required>
                </div>
                
                <button type="submit" class="btn-submit">💾 บันทึกรหัสผ่านใหม่</button>
                <a href="index.php" class="btn-back">← กลับหน้าหลัก</a>
            </form>
        </div>
    </div>
</body>
</html>

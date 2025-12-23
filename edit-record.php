<?php
require_once 'config.php';

// Get date parameter
$date = $_GET['date'] ?? date('Y-m-d');

// Validate date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    header('Location: /view-records.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance()->getConnection();
        
        if (isset($_POST['action']) && $_POST['action'] === 'update') {
            $db->beginTransaction();
            
            foreach ($_POST['quantities'] as $materialId => $quantity) {
                $quantity = (float)$quantity;
                
                if ($quantity >= 0) {
                    $stmt = $db->prepare("
                        UPDATE daily_stock_records 
                        SET remaining_quantity = ? 
                        WHERE record_date = ? AND material_id = ?
                    ");
                    $stmt->execute([$quantity, $date, $materialId]);
                }
            }
            
            $db->commit();
            $success = "แก้ไขข้อมูลสำเร็จ";
        }
        
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $error = $e->getMessage();
    }
}

// Get records for the date
try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT 
            dsr.record_date,
            dsr.material_id,
            dsr.remaining_quantity,
            dsr.photo_path,
            dsr.submitted_at,
            e.full_name as employee_name,
            rm.material_name,
            rm.unit
        FROM daily_stock_records dsr
        JOIN employees e ON dsr.employee_id = e.id
        JOIN raw_materials rm ON dsr.material_id = rm.id
        WHERE dsr.record_date = ?
        ORDER BY rm.display_order ASC, rm.material_name ASC
    ");
    $stmt->execute([$date]);
    $records = $stmt->fetchAll();
    
    if (empty($records)) {
        header('Location: /view-records.php?error=no_data');
        exit;
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
    $records = [];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลสต็อก - Hazel</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .edit-form {
            max-width: 800px;
            margin: 0 auto;
        }
        .material-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 100px;
            gap: 1rem;
            align-items: center;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            background: white;
        }
        .material-row:hover {
            background: #f9fafb;
        }
        .quantity-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            text-align: right;
            font-family: monospace;
            font-size: 1rem;
        }
        .quantity-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .photo-thumb {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 0.375rem;
            border: 1px solid #d1d5db;
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
        .alert-warning {
            background: #fffbeb;
            border: 1px solid #f59e0b;
            color: #92400e;
        }
        .btn-save {
            background: #10b981;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-save:hover {
            background: #059669;
            transform: translateY(-1px);
        }
        .btn-cancel {
            background: #6b7280;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn-cancel:hover {
            background: #4b5563;
            transform: translateY(-1px);
        }
        @media (max-width: 768px) {
            .material-row {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="app-header hazel-header">
            <img src="assets/hazel-logo.png" alt="Hazel" class="hazel-logo">
            <div class="hazel-subtitle">Beverages & Appetizers</div>
            <h1>แก้ไขข้อมูลสต็อก</h1>
            <div class="current-date">วันที่ <?= date('d/m/Y', strtotime($date)) ?></div>
        </div>
        
        <div class="employee-section">
            <!-- Navigation -->
            <div class="material-card mb-4">
                <div class="flex justify-between items-center">
                    <a href="/view-records.php?date=<?= $date ?>" class="text-blue-600 hover:text-blue-800">← กลับดูข้อมูล</a>
                    <div class="space-x-2">
                        <a href="/" class="text-blue-600 hover:text-blue-800 text-sm">🏠 หน้าหลัก</a>
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

            <!-- Warning -->
            <div class="alert alert-warning">
                ⚠️ <strong>คำเตือน:</strong> การแก้ไขข้อมูลจะส่งผลต่อรายงานและสถิติ กรุณาตรวจสอบให้แน่ใจก่อนบันทึก
            </div>

            <!-- Edit Form -->
            <?php if (!empty($records)): ?>
                <div class="material-card">
                    <div class="text-center mb-6">
                        <h3 class="text-lg font-semibold">แก้ไขข้อมูลสต็อกวันที่ <?= date('d/m/Y', strtotime($date)) ?></h3>
                        <p class="text-gray-600">พนักงาน: <strong class="text-red-600"><?= htmlspecialchars($records[0]['employee_name']) ?></strong></p>
                        <p class="text-sm text-gray-500">บันทึกเมื่อ: <?= date('d/m/Y H:i น.', strtotime($records[0]['submitted_at'])) ?></p>
                    </div>
                    
                    <form method="POST" class="edit-form">
                        <input type="hidden" name="action" value="update">
                        
                        <!-- Header -->
                        <div class="material-row" style="background: #f9fafb; font-weight: 600; margin-bottom: 0.5rem;">
                            <div>วัตถุดิบ</div>
                            <div class="text-center">จำนวนคงเหลือ</div>
                            <div class="text-center">หน่วย</div>
                            <div class="text-center">รูปภาพ</div>
                        </div>
                        
                        <!-- Data Rows -->
                        <?php foreach ($records as $record): ?>
                            <div class="material-row">
                                <div>
                                    <div class="font-semibold"><?= htmlspecialchars($record['material_name']) ?></div>
                                    <div class="text-sm text-gray-500">ID: <?= $record['material_id'] ?></div>
                                </div>
                                
                                <div>
                                    <input type="number" 
                                           name="quantities[<?= $record['material_id'] ?>]"
                                           value="<?= number_format($record['remaining_quantity'], 2, '.', '') ?>"
                                           class="quantity-input"
                                           min="0" 
                                           step="0.01"
                                           required>
                                </div>
                                
                                <div class="text-center">
                                    <?= htmlspecialchars($record['unit']) ?>
                                </div>
                                
                                <div class="text-center">
                                    <?php if (!empty($record['photo_path']) && $record['photo_path'] !== 'no-photo.jpg'): ?>
                                        <?php 
                                        $photoPath = 'stock-photos/' . $record['photo_path'];
                                        if (file_exists($photoPath)): 
                                        ?>
                                            <img src="<?= $photoPath ?>" alt="รูปสต็อก" class="photo-thumb">
                                        <?php else: ?>
                                            <span class="text-gray-500 text-xs">ไม่พบรูป</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-500 text-xs">ไม่มีรูป</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Action Buttons -->
                        <div class="text-center mt-6 space-x-4">
                            <button type="submit" class="btn-save" onclick="return confirm('คุณต้องการบันทึกการแก้ไขหรือไม่?')">
                                💾 บันทึกการแก้ไข
                            </button>
                            <a href="/view-records.php?date=<?= $date ?>" class="btn-cancel">
                                ❌ ยกเลิก
                            </a>
                            <button type="button" onclick="deleteRecord('<?= $date ?>')" 
                                    class="bg-red-500 text-white px-8 py-3 rounded text-sm hover:bg-red-600 transition-colors">
                                🗑️ ลบข้อมูลทั้งหมด
                            </button>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <p class="text-xs text-gray-500">
                                💡 เคล็ดลับ: ใช้ Tab เพื่อเปลี่ยนช่องกรอกข้อมูล | กด Ctrl+S เพื่อบันทึกเร็ว
                            </p>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+S to save
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                if (confirm('คุณต้องการบันทึกการแก้ไขหรือไม่?')) {
                    document.querySelector('form').submit();
                }
            }
            
            // Escape to cancel
            if (e.key === 'Escape') {
                if (confirm('คุณต้องการยกเลิกการแก้ไขหรือไม่?')) {
                    window.location.href = '/view-records.php?date=<?= $date ?>';
                }
            }
        });
        
        // Auto-select text when focus
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.select();
            });
        });
        
        // Highlight changed values
        document.querySelectorAll('.quantity-input').forEach(input => {
            const originalValue = input.value;
            input.addEventListener('input', function() {
                if (this.value !== originalValue) {
                    this.style.backgroundColor = '#fef3c7';
                    this.style.borderColor = '#f59e0b';
                } else {
                    this.style.backgroundColor = '';
                    this.style.borderColor = '';
                }
            });
        });
        
        // Delete record function
        async function deleteRecord(date) {
            const thaiDate = new Date(date).toLocaleDateString('th-TH');
            
            if (!confirm(`⚠️ คุณต้องการลบข้อมูลสต็อกวันที่ ${thaiDate} หรือไม่?\n\n🚨 การลบจะไม่สามารถกู้คืนได้!\n\n✅ หลังจากลบแล้ว คุณสามารถบันทึกข้อมูลใหม่ได้`)) {
                return;
            }
            
            if (!confirm(`🔴 ยืนยันอีกครั้ง: ลบข้อมูลวันที่ ${thaiDate}?\n\nข้อมูลทั้งหมดรวมถึงรูปภาพจะถูกลบถาวร`)) {
                return;
            }
            
            try {
                const response = await fetch('/api/delete-record.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ date: date })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(`✅ ลบข้อมูลสำเร็จ!\n\n📊 ลบข้อมูล: ${data.details.deleted_records} รายการ\n📸 ลบรูปภาพ: ${data.details.deleted_photos} รูป\n\n🎯 ตอนนี้คุณสามารถบันทึกข้อมูลใหม่ได้แล้ว`);
                    window.location.href = '/';
                } else {
                    alert('❌ เกิดข้อผิดพลาด: ' + data.message);
                }
                
            } catch (error) {
                console.error('Delete error:', error);
                alert('❌ เกิดข้อผิดพลาด: ' + error.message);
            }
        }
    </script>
</body>
</html>
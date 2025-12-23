<?php
require_once 'config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get date filter
    $date = $_GET['date'] ?? date('Y-m-d');
    
    // Get records for the date
    $stmt = $db->prepare("
        SELECT 
            dsr.record_date,
            dsr.remaining_quantity,
            dsr.photo_path,
            dsr.submitted_at,
            e.full_name as employee_name,
            rm.material_name,
            rm.unit,
            rm.sub_unit
        FROM daily_stock_records dsr
        JOIN employees e ON dsr.employee_id = e.id
        JOIN raw_materials rm ON dsr.material_id = rm.id
        WHERE dsr.record_date = ?
        ORDER BY rm.display_order ASC, rm.material_name ASC
    ");
    $stmt->execute([$date]);
    $records = $stmt->fetchAll();
    
    // Get available dates
    $stmt = $db->query("
        SELECT DISTINCT record_date 
        FROM daily_stock_records 
        ORDER BY record_date DESC 
        LIMIT 30
    ");
    $dates = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ดูข้อมูลสต็อก - Hazel</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Footer Styles */
        .hazel-footer {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            margin-top: 4rem;
            padding: 3rem 0 1rem 0;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
        }
        
        .footer-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .footer-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
        
        .footer-text h3 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .footer-text p {
            margin: 0.25rem 0;
            opacity: 0.9;
        }
        
        .footer-tagline {
            font-style: italic;
            font-size: 0.875rem;
            opacity: 0.8;
        }
        
        .footer-right {
            flex: 1;
            max-width: 400px;
        }
        
        .owner-section {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 1rem;
            backdrop-filter: blur(10px);
        }
        
        .owner-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .owner-info h4 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .owner-info p {
            margin: 0.25rem 0;
            opacity: 0.9;
        }
        
        .owner-quote {
            font-style: italic;
            font-size: 0.875rem;
            opacity: 0.8;
            margin-top: 0.5rem;
            line-height: 1.4;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 2rem;
            padding-top: 1rem;
            text-align: center;
            opacity: 0.8;
        }
        
        .footer-bottom p {
            margin: 0.25rem 0;
            font-size: 0.875rem;
        }
        
        .footer-system {
            opacity: 0.6;
        }
        
        /* Responsive Footer */
        @media (max-width: 768px) {
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-left {
                flex-direction: column;
                text-align: center;
            }
            
            .owner-section {
                flex-direction: column;
                text-align: center;
            }
            
            .owner-photo {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="app-header hazel-header">
            <img src="assets/hazel-logo.png" alt="Hazel" class="hazel-logo">
            <div class="hazel-subtitle">Beverages & Appetizers</div>
            <h1>ดูข้อมูลสต็อก</h1>
        </div>
        
        <div class="employee-section">
            <!-- Navigation -->
            <div class="material-card mb-4">
                <div class="flex justify-between items-center mb-4">
                    <a href="/" class="text-blue-600 hover:text-blue-800">← กลับหน้าหลัก</a>
                    <div class="space-x-2">
                        <a href="/manage-employees.php" class="text-green-600 hover:text-green-800 text-sm">👥 จัดการพนักงาน</a>
                        <a href="/manage-materials.php" class="text-purple-600 hover:text-purple-800 text-sm">🧪 จัดการวัตถุดิบ</a>
                        <a href="/add-stock.php" class="text-orange-600 hover:text-orange-800 text-sm">📦 เพิ่มสต็อกเข้า</a>
                        <a href="/setup.php" class="text-gray-600 hover:text-gray-800 text-sm">🛠️ Setup</a>
                    </div>
                </div>
                
                <!-- Date Filter -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">เลือกวันที่:</label>
                    <select id="dateFilter" class="form-input" onchange="changeDate()">
                        <?php foreach ($dates as $d): ?>
                            <option value="<?= $d['record_date'] ?>" <?= $d['record_date'] === $date ? 'selected' : '' ?>>
                                <?= date('d/m/Y', strtotime($d['record_date'])) ?>
                                <?= $d['record_date'] === date('Y-m-d') ? ' (วันนี้)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="material-card bg-red-50 border-red-200">
                    <p class="text-red-700">เกิดข้อผิดพลาด: <?= htmlspecialchars($error) ?></p>
                </div>
            <?php elseif (empty($records)): ?>
                <div class="material-card text-center">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                    <h3 class="text-lg font-semibold mb-2">ไม่มีข้อมูลสต็อก</h3>
                    <p class="text-gray-600 mb-4">วันที่ <?= date('d/m/Y', strtotime($date)) ?></p>
                    <?php if (isset($_GET['error']) && $_GET['error'] === 'no_data'): ?>
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                            ⚠️ ไม่สามารถแก้ไขได้เพราะไม่มีข้อมูลสำหรับวันที่นี้
                        </div>
                    <?php endif; ?>
                    <a href="/" class="btn-primary">บันทึกสต็อกใหม่</a>
                </div>
            <?php else: ?>
                <!-- Records Display -->
                <div class="material-card">
                    <div class="text-center mb-4">
                        <h3 class="text-lg font-semibold">ข้อมูลสต็อกวันที่ <?= date('d/m/Y', strtotime($date)) ?></h3>
                        <p class="text-gray-600">พนักงาน: <strong class="text-red-600"><?= htmlspecialchars($records[0]['employee_name']) ?></strong></p>
                        <p class="text-sm text-gray-500">บันทึกเมื่อ: <?= date('H:i น.', strtotime($records[0]['submitted_at'])) ?></p>
                        
                        <!-- Edit Button -->
                        <div class="mt-3 space-x-2">
                            <a href="/edit-record.php?date=<?= $date ?>" 
                               class="inline-block bg-orange-500 text-white px-4 py-2 rounded text-sm hover:bg-orange-600 transition-colors">
                                ✏️ แก้ไขข้อมูล
                            </a>
                            <button onclick="deleteRecord('<?= $date ?>')" 
                                    class="bg-red-500 text-white px-4 py-2 rounded text-sm hover:bg-red-600 transition-colors">
                                🗑️ ลบข้อมูล
                            </button>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="border border-gray-300 px-4 py-2 text-left">วัตถุดิบ</th>
                                    <th class="border border-gray-300 px-4 py-2 text-center">หน่วย</th>
                                    <th class="border border-gray-300 px-4 py-2 text-right">จำนวนคงเหลือ</th>
                                    <th class="border border-gray-300 px-4 py-2 text-center">รูปภาพ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($record['material_name']) ?></td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <?= htmlspecialchars($record['unit']) ?>
                                            <?php if ($record['sub_unit']): ?>
                                                <br><span class="text-xs text-gray-600"><?= htmlspecialchars($record['sub_unit']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-right font-mono">
                                            <?= number_format($record['remaining_quantity'], 2) ?>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <?php if (!empty($record['photo_path']) && $record['photo_path'] !== 'no-photo.jpg'): ?>
                                                <?php 
                                                $photoPath = 'stock-photos/' . $record['photo_path'];
                                                if (file_exists($photoPath)): 
                                                ?>
                                                    <img src="<?= $photoPath ?>" 
                                                         alt="รูปสต็อก" 
                                                         class="w-16 h-12 object-cover rounded border cursor-pointer"
                                                         onclick="showPhotoModal('<?= $photoPath ?>', '<?= htmlspecialchars($record['material_name']) ?>')">
                                                <?php else: ?>
                                                    <span class="text-red-500 text-xs">ไม่พบรูป</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-gray-500 text-xs">ไม่มีรูป</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-600 mb-3">รวม <?= count($records) ?> รายการ</p>
                        <div class="space-x-2">
                            <a href="/api/export-csv.php?date=<?= $date ?>" 
                               class="btn-primary" 
                               style="background: #10b981; display: inline-block; text-decoration: none;">
                                📊 Export CSV (Excel ใช้ได้)
                            </a>
                            <a href="/api/export-usage-report.php?end_date=<?= $date ?>" 
                               class="btn-primary" 
                               style="background: #f59e0b; display: inline-block; text-decoration: none;">
                                📈 รายงานการใช้งาน (เปรียบเทียบ)
                            </a>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">ไฟล์ CSV สามารถเปิดใน Excel ได้ทันที</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <footer class="hazel-footer">
            <div class="footer-content">
                <div class="footer-left">
                    <img src="assets/hazel-logo.png" alt="Hazel" class="footer-logo">
                    <div class="footer-text">
                        <h3>Hazel</h3>
                        <p>Beverages & Appetizers</p>
                        <p class="footer-tagline">คุณภาพในทุกหยด ความอร่อยในทุกคำ</p>
                    </div>
                </div>
                <div class="footer-right">
                    <div class="owner-section">
                        <img src="assets/phuriboss.jpg" alt="Owner" class="owner-photo">
                        <div class="owner-info">
                            <h4>ภูริวัฒน์ โภคสวัสดิ์</h4>
                            <p>ผู้อยู่เบื้องหลังกิจการ</p>
                            <p class="owner-quote">"มุ่งมั่นสร้างสรรค์เครื่องดื่มคุณภาพ<br>เพื่อความสุขของลูกค้าทุกท่าน"</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Hazel Beverages & Appetizers. สงวนลิขสิทธิ์.</p>
                <p class="footer-system">ระบบจัดการสต็อกวัตถุดิบ | พัฒนาโดย Kiro AI</p>
            </div>
        </footer>
    </div>

    <script>
        function changeDate() {
            const date = document.getElementById('dateFilter').value;
            window.location.href = '?date=' + date;
        }
        
        async function deleteRecord(date) {
            const thaiDate = new Date(date).toLocaleDateString('th-TH');
            
            if (!confirm(`⚠️ คุณต้องการลบข้อมูลสต็อกวันที่ ${thaiDate} หรือไม่?\n\n🚨 การลบจะไม่สามารถกู้คืนได้!\n\n✅ หลังจากลบแล้ว คุณสามารถบันทึกข้อมูลใหม่ได้`)) {
                return;
            }
            
            if (!confirm(`🔴 ยืนยันอีกครั้ง: ลบข้อมูลวันที่ ${thaiDate}?\n\nข้อมูลทั้งหมดรวมถึงรูปภาพจะถูกลบถาวร`)) {
                return;
            }
            
            try {
                // Show loading
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = '⏳ กำลังลบ...';
                button.disabled = true;
                
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
                    
                    // Redirect to main page or refresh
                    window.location.href = '/';
                } else {
                    alert('❌ เกิดข้อผิดพลาด: ' + data.message);
                    button.textContent = originalText;
                    button.disabled = false;
                }
                
            } catch (error) {
                console.error('Delete error:', error);
                alert('❌ เกิดข้อผิดพลาด: ' + error.message);
                button.textContent = originalText;
                button.disabled = false;
            }
        }
        
        // Photo modal functions
        function showPhotoModal(photoPath, materialName) {
            document.getElementById('photoModalImg').src = photoPath;
            document.getElementById('photoTitle').textContent = 'รูปภาพ: ' + materialName;
            document.getElementById('photoModal').classList.remove('hidden');
        }
        
        function closePhotoModal() {
            document.getElementById('photoModal').classList.add('hidden');
        }
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePhotoModal();
            }
        });
    </script>

    <!-- Photo Modal -->
    <div id="photoModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center" onclick="closePhotoModal()">
        <div class="max-w-4xl max-h-full p-4" onclick="event.stopPropagation()">
            <div class="bg-white rounded-lg overflow-hidden">
                <div class="p-4 border-b flex justify-between items-center">
                    <h3 id="photoTitle" class="text-lg font-semibold"></h3>
                    <button onclick="closePhotoModal()" class="text-gray-500 hover:text-gray-700 text-xl">✕</button>
                </div>
                <div class="p-4">
                    <img id="photoModalImg" src="" alt="" class="max-w-full max-h-96 mx-auto rounded">
                </div>
            </div>
        </div>
    </div>
</body>
</html>
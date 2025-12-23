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
            e.full_name as employee_name,
            rm.material_name,
            rm.unit,
            dsr.remaining_quantity,
            dsr.submitted_at
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
                    <a href="/setup.php" class="text-gray-600 hover:text-gray-800 text-sm">🛠️ Setup</a>
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
                    <a href="/" class="btn-primary">บันทึกสต็อกใหม่</a>
                </div>
            <?php else: ?>
                <!-- Records Display -->
                <div class="material-card">
                    <div class="text-center mb-4">
                        <h3 class="text-lg font-semibold">ข้อมูลสต็อกวันที่ <?= date('d/m/Y', strtotime($date)) ?></h3>
                        <p class="text-gray-600">พนักงาน: <strong class="text-red-600"><?= htmlspecialchars($records[0]['employee_name']) ?></strong></p>
                        <p class="text-sm text-gray-500">บันทึกเมื่อ: <?= date('H:i น.', strtotime($records[0]['submitted_at'])) ?></p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="border border-gray-300 px-4 py-2 text-left">วัตถุดิบ</th>
                                    <th class="border border-gray-300 px-4 py-2 text-center">หน่วย</th>
                                    <th class="border border-gray-300 px-4 py-2 text-right">จำนวนคงเหลือ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($record['material_name']) ?></td>
                                        <td class="border border-gray-300 px-4 py-2 text-center"><?= htmlspecialchars($record['unit']) ?></td>
                                        <td class="border border-gray-300 px-4 py-2 text-right font-mono">
                                            <?= number_format($record['remaining_quantity'], 2) ?>
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
                                📊 Export CSV
                            </a>
                            <a href="/api/export-excel.php?date=<?= $date ?>" 
                               class="btn-primary" 
                               style="background: #3b82f6; display: inline-block; text-decoration: none;">
                                📈 Export Excel
                            </a>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">CSV: ใช้งานได้เลย | Excel: ต้องติดตั้ง PhpSpreadsheet</p>
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
                            <p>เจ้าของกิจการ</p>
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
    </script>
</body>
</html>
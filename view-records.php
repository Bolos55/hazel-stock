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
                        <p class="text-sm text-gray-600">รวม <?= count($records) ?> รายการ</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function changeDate() {
            const date = document.getElementById('dateFilter').value;
            window.location.href = '?date=' + date;
        }
    </script>
</body>
</html>
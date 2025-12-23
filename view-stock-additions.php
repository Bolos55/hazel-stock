<?php
require_once 'config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get pagination parameters
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 50;
    $offset = ($page - 1) * $limit;
    
    // Get total count
    $stmt = $db->query("SELECT COUNT(*) FROM stock_additions");
    $totalRecords = $stmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);
    
    // Get stock additions with pagination
    try {
        $stmt = $db->prepare("
            SELECT 
                sa.quantity,
                sa.note,
                sa.added_at,
                rm.material_name,
                rm.unit,
                COALESCE(rm.sub_unit, '') as sub_unit,
                e.employee_name
            FROM stock_additions sa
            JOIN raw_materials rm ON sa.material_id = rm.id
            JOIN employees e ON sa.employee_id = e.id
            ORDER BY sa.added_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $additions = $stmt->fetchAll();
    } catch (Exception $e) {
        // Fallback if sub_unit doesn't exist
        $stmt = $db->prepare("
            SELECT 
                sa.quantity,
                sa.note,
                sa.added_at,
                rm.material_name,
                rm.unit,
                '' as sub_unit,
                e.employee_name
            FROM stock_additions sa
            JOIN raw_materials rm ON sa.material_id = rm.id
            JOIN employees e ON sa.employee_id = e.id
            ORDER BY sa.added_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $additions = $stmt->fetchAll();
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
    $additions = [];
    $totalRecords = 0;
    $totalPages = 0;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการเพิ่มสต็อกทั้งหมด - Hazel</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            text-decoration: none;
            color: #374151;
        }
        .pagination a:hover {
            background: #f3f4f6;
        }
        .pagination .current {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #1e40af;
        }
        .stat-label {
            color: #64748b;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="app-header hazel-header">
            <img src="assets/hazel-logo.png" alt="Hazel" class="hazel-logo">
            <div class="hazel-subtitle">Beverages & Appetizers</div>
            <h1>ประวัติการเพิ่มสต็อกทั้งหมด</h1>
        </div>
        
        <div class="employee-section">
            <!-- Navigation -->
            <div class="material-card mb-4">
                <div class="flex justify-between items-center">
                    <a href="/add-stock.php" class="text-blue-600 hover:text-blue-800">← กลับหน้าเพิ่มสต็อก</a>
                    <div class="space-x-2">
                        <a href="/" class="text-blue-600 hover:text-blue-800 text-sm">🏠 หน้าหลัก</a>
                        <a href="/view-records.php" class="text-blue-600 hover:text-blue-800 text-sm">📊 ดูข้อมูลสต็อก</a>
                    </div>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="material-card mb-4">
                    <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($totalRecords) ?></div>
                    <div class="stat-label">รายการทั้งหมด</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $totalPages ?></div>
                    <div class="stat-label">หน้าทั้งหมด</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= count($additions) ?></div>
                    <div class="stat-label">แสดงในหน้านี้</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $page ?></div>
                    <div class="stat-label">หน้าปัจจุบัน</div>
                </div>
            </div>

            <!-- Stock Additions List -->
            <div class="material-card">
                <h3 class="text-lg font-semibold mb-4">
                    📋 ประวัติการเพิ่มสต็อก 
                    <span class="text-sm font-normal text-gray-600">
                        (หน้า <?= $page ?> จาก <?= $totalPages ?>)
                    </span>
                </h3>
                
                <?php if (empty($additions)): ?>
                    <div class="text-center py-8">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📦</div>
                        <p class="text-gray-600">ไม่พบข้อมูลการเพิ่มสต็อก</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="stock-table">
                            <thead>
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>วันที่/เวลา</th>
                                    <th>วัตถุดิบ</th>
                                    <th>จำนวน</th>
                                    <th>หน่วย</th>
                                    <th>พนักงาน</th>
                                    <th>หมายเหตุ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($additions as $index => $addition): ?>
                                    <tr>
                                        <td class="text-center"><?= $offset + $index + 1 ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($addition['added_at'])) ?></td>
                                        <td class="font-semibold"><?= htmlspecialchars($addition['material_name']) ?></td>
                                        <td class="text-right font-mono text-green-600">+<?= number_format($addition['quantity'], 2) ?></td>
                                        <td>
                                            <?= htmlspecialchars($addition['unit']) ?>
                                            <?php if ($addition['sub_unit']): ?>
                                                <br><span class="text-xs text-gray-600"><?= htmlspecialchars($addition['sub_unit']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($addition['employee_name']) ?></td>
                                        <td>
                                            <?php if ($addition['note']): ?>
                                                <?= htmlspecialchars($addition['note']) ?>
                                            <?php else: ?>
                                                <span class="text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=1">« แรก</a>
                                <a href="?page=<?= $page - 1 ?>">‹ ก่อนหน้า</a>
                            <?php endif; ?>
                            
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                            
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= $page + 1 ?>">ถัดไป ›</a>
                                <a href="?page=<?= $totalPages ?>">สุดท้าย »</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
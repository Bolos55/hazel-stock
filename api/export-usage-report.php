<?php
require_once '../config.php';
require_once '../work-date-helper.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get date range (default: yesterday and today)
    $endDate = $_GET['end_date'] ?? getWorkDate();
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime($endDate . ' -1 day'));
    
    // Get all materials with their order
    $stmt = $db->query("
        SELECT id, material_name, unit, sub_unit, display_order
        FROM raw_materials
        ORDER BY display_order ASC
    ");
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get stock records for both dates
    $stmt = $db->prepare("
        SELECT 
            dsr.material_id,
            dsr.record_date,
            dsr.quantity_main,
            dsr.quantity_sub,
            e.employee_name
        FROM daily_stock_records dsr
        JOIN employees e ON dsr.employee_id = e.id
        WHERE dsr.record_date IN (?, ?)
        ORDER BY dsr.record_date ASC
    ");
    $stmt->execute([$startDate, $endDate]);
    $records = $stmt->fetchAll(PDO::FETCH_GROUP);
    
    // Get stock additions between dates
    $stmt = $db->prepare("
        SELECT 
            material_id,
            SUM(quantity) as total_added
        FROM stock_additions
        WHERE DATE(added_at) BETWEEN ? AND ?
        GROUP BY material_id
    ");
    $stmt->execute([$startDate, $endDate]);
    $additions = $stmt->fetchAll(PDO::FETCH_GROUP);
    
    // Generate CSV
    $filename = "usage_report_{$startDate}_to_{$endDate}.csv";
    
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Add BOM for UTF-8
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    
    // Title
    fputcsv($output, ['รายงานการใช้วัตถุดิบ - Hazel Beverages & Appetizers']);
    fputcsv($output, ['ระหว่างวันที่: ' . date('d/m/Y', strtotime($startDate)) . ' ถึง ' . date('d/m/Y', strtotime($endDate))]);
    fputcsv($output, ['สร้างเมื่อ: ' . date('d/m/Y H:i:s')]);
    fputcsv($output, []); // Empty row
    
    // Headers
    fputcsv($output, [
        'ลำดับ',
        'วัตถุดิบ',
        'หน่วยหลัก',
        'หน่วยย่อย',
        'สต็อกเริ่มต้น (หน่วยหลัก)',
        'สต็อกเริ่มต้น (หน่วยย่อย)',
        'เพิ่มเข้า (หน่วยหลัก)',
        'เพิ่มเข้า (หน่วยย่อย)',
        'สต็อกสิ้นสุด (หน่วยหลัก)',
        'สต็อกสิ้นสุด (หน่วยย่อย)',
        'ใช้ไป (หน่วยหลัก)',
        'ใช้ไป (หน่วยย่อย)',
        'พนักงานบันทึก',
        'สถานะ'
    ]);
    
    $rowNum = 1;
    foreach ($materials as $material) {
        $materialId = $material['id'];
        
        // Get start stock (from start date)
        $startMain = 0;
        $startSub = 0;
        $employeeName = '-';
        
        if (isset($records[$materialId])) {
            foreach ($records[$materialId] as $record) {
                if ($record['record_date'] === $startDate) {
                    $startMain = $record['quantity_main'] ?? 0;
                    $startSub = $record['quantity_sub'] ?? 0;
                    $employeeName = $record['employee_name'];
                }
            }
        }
        
        // Get end stock (from end date)
        $endMain = 0;
        $endSub = 0;
        
        if (isset($records[$materialId])) {
            foreach ($records[$materialId] as $record) {
                if ($record['record_date'] === $endDate) {
                    $endMain = $record['quantity_main'] ?? 0;
                    $endSub = $record['quantity_sub'] ?? 0;
                    $employeeName = $record['employee_name'];
                }
            }
        }
        
        // Get additions
        $addedMain = 0;
        $addedSub = 0;
        
        if (isset($additions[$materialId]) && !empty($additions[$materialId])) {
            // For now, assume additions go to main unit
            // In future, you may want to add quantity_main and quantity_sub to stock_additions table
            $addedMain = $additions[$materialId][0]['total_added'] ?? 0;
        }
        
        // Calculate usage: Start + Added - End = Used
        $usedMain = $startMain + $addedMain - $endMain;
        $usedSub = $startSub + $addedSub - $endSub;
        
        // Determine status
        $status = '';
        if ($usedMain > 0 || $usedSub > 0) {
            $status = '✓ ใช้งานปกติ';
        } elseif ($usedMain < 0 || $usedSub < 0) {
            $status = '⚠ เพิ่มขึ้น (รับของเข้า)';
        } else {
            $status = '- ไม่มีการใช้งาน';
        }
        
        fputcsv($output, [
            $rowNum++,
            $material['material_name'],
            $material['unit'] ?: '-',
            $material['sub_unit'] ?: '-',
            number_format($startMain, 2),
            number_format($startSub, 2),
            number_format($addedMain, 2),
            number_format($addedSub, 2),
            number_format($endMain, 2),
            number_format($endSub, 2),
            number_format($usedMain, 2),
            number_format($usedSub, 2),
            $employeeName,
            $status
        ]);
    }
    
    // Summary
    fputcsv($output, []); // Empty row
    fputcsv($output, ['สรุป']);
    fputcsv($output, ['วันที่เริ่มต้น:', date('d/m/Y', strtotime($startDate))]);
    fputcsv($output, ['วันที่สิ้นสุด:', date('d/m/Y', strtotime($endDate))]);
    fputcsv($output, ['จำนวนวัตถุดิบทั้งหมด:', count($materials), 'รายการ']);
    
    fclose($output);
    
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
?>
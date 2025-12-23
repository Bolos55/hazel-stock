<?php
require_once '../config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get date range (default: yesterday and today)
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime($endDate . ' -1 day'));
    
    // Get stock records for both dates
    $stmt = $db->prepare("
        SELECT 
            rm.material_name,
            rm.unit,
            rm.sub_unit,
            dsr.record_date,
            dsr.remaining_quantity,
            e.employee_name
        FROM daily_stock_records dsr
        JOIN raw_materials rm ON dsr.material_id = rm.id
        JOIN employees e ON dsr.employee_id = e.id
        WHERE dsr.record_date IN (?, ?)
        ORDER BY rm.display_order ASC, dsr.record_date ASC
    ");
    $stmt->execute([$startDate, $endDate]);
    $records = $stmt->fetchAll();
    
    // Get stock additions between dates
    $stmt = $db->prepare("
        SELECT 
            rm.material_name,
            SUM(sa.quantity) as total_added
        FROM stock_additions sa
        JOIN raw_materials rm ON sa.material_id = rm.id
        WHERE DATE(sa.added_at) BETWEEN ? AND ?
        GROUP BY sa.material_id, rm.material_name
    ");
    $stmt->execute([$startDate, $endDate]);
    $additions = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Process data for usage calculation
    $usageData = [];
    foreach ($records as $record) {
        $materialName = $record['material_name'];
        if (!isset($usageData[$materialName])) {
            $usageData[$materialName] = [
                'material_name' => $materialName,
                'unit' => $record['unit'],
                'sub_unit' => $record['sub_unit'],
                'start_stock' => 0,
                'end_stock' => 0,
                'added_stock' => $additions[$materialName] ?? 0,
                'employee' => $record['employee_name']
            ];
        }
        
        if ($record['record_date'] === $startDate) {
            $usageData[$materialName]['start_stock'] = $record['remaining_quantity'];
        } elseif ($record['record_date'] === $endDate) {
            $usageData[$materialName]['end_stock'] = $record['remaining_quantity'];
        }
    }
    
    // Generate CSV
    $filename = "usage_report_{$startDate}_to_{$endDate}.csv";
    
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Add BOM for UTF-8
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    
    // Headers
    fputcsv($output, [
        'วัตถุดิบ',
        'หน่วยหลัก',
        'หน่วยย่อย',
        'สต็อกเริ่มต้น (' . date('d/m/Y', strtotime($startDate)) . ')',
        'สต็อกสิ้นสุด (' . date('d/m/Y', strtotime($endDate)) . ')',
        'เพิ่มเข้า',
        'ใช้ไป',
        'พนักงานบันทึก',
        'หมายเหตุ'
    ]);
    
    foreach ($usageData as $data) {
        $used = $data['start_stock'] + $data['added_stock'] - $data['end_stock'];
        $note = '';
        
        if ($used > 0) {
            $note = 'ใช้งานปกติ';
        } elseif ($used < 0) {
            $note = 'เพิ่มขึ้น (รับของเข้าหรือแก้ไขข้อมูล)';
        } else {
            $note = 'ไม่มีการใช้งาน';
        }
        
        fputcsv($output, [
            $data['material_name'],
            $data['unit'],
            $data['sub_unit'] ?: '-',
            number_format($data['start_stock'], 2),
            number_format($data['end_stock'], 2),
            number_format($data['added_stock'], 2),
            number_format($used, 2),
            $data['employee'],
            $note
        ]);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
?>
<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../work-date-helper.php';

try {
    $db = Database::getInstance()->getConnection();
    $date = $_GET['date'] ?? getWorkDate();
    
    // Validate date
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        throw new Exception('Invalid date format');
    }
    
    // Get records
    $stmt = $db->prepare("
        SELECT 
            dsr.record_date,
            e.full_name as employee_name,
            rm.material_name,
            rm.unit,
            rm.sub_unit,
            dsr.quantity_main,
            dsr.quantity_sub,
            dsr.photo_path,
            dsr.submitted_at,
            rm.display_order
        FROM daily_stock_records dsr
        JOIN employees e ON dsr.employee_id = e.id
        JOIN raw_materials rm ON dsr.material_id = rm.id
        WHERE dsr.record_date = ?
        ORDER BY rm.display_order ASC
    ");
    $stmt->execute([$date]);
    $records = $stmt->fetchAll();
    
    if (empty($records)) {
        throw new Exception('ไม่มีข้อมูลสำหรับวันที่เลือก');
    }
    
    // Set headers for CSV download
    $filename = "hazel_stock_" . $date . ".csv";
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Add BOM for UTF-8
    echo "\xEF\xBB\xBF";
    
    // Create CSV content
    $output = fopen('php://output', 'w');
    
    // Header
    fputcsv($output, ['Hazel - Beverages & Appetizers']);
    fputcsv($output, ['บันทึกสต็อกวัตถุดิบ วันที่ ' . date('d/m/Y', strtotime($date))]);
    fputcsv($output, ['พนักงาน: ' . $records[0]['employee_name']]);
    fputcsv($output, ['บันทึกเมื่อ: ' . date('d/m/Y H:i น.', strtotime($records[0]['submitted_at']))]);
    fputcsv($output, []); // Empty line
    
    // Table header
    fputcsv($output, [
        'ลำดับ', 
        'วัตถุดิบ', 
        'จำนวน (หน่วยหลัก)', 
        'หน่วยหลัก',
        'จำนวน (หน่วยย่อย)', 
        'หน่วยย่อย',
        'มีรูปภาพ',
        'หมายเหตุ'
    ]);
    
    // Data rows
    $i = 1;
    foreach ($records as $record) {
        $hasPhoto = ($record['photo_path'] && $record['photo_path'] !== 'no-photo.jpg') ? '✓' : '-';
        
        // Create note based on quantities
        $note = '';
        if ($record['quantity_main'] == 0 && $record['quantity_sub'] == 0) {
            $note = 'ไม่มีสต็อก';
        } elseif ($record['quantity_main'] > 0 && $record['quantity_sub'] > 0) {
            $note = 'มีสต็อกครบ';
        } elseif ($record['quantity_main'] > 0) {
            $note = 'มีเฉพาะหน่วยหลัก';
        } elseif ($record['quantity_sub'] > 0) {
            $note = 'มีเฉพาะหน่วยย่อย';
        }
        
        fputcsv($output, [
            $i++,
            $record['material_name'],
            number_format($record['quantity_main'], 2),
            $record['unit'] ?: '-',
            number_format($record['quantity_sub'], 2),
            $record['sub_unit'] ?: '-',
            $hasPhoto,
            $note
        ]);
    }
    
    // Summary
    fputcsv($output, []); // Empty line
    fputcsv($output, ['สรุป']);
    fputcsv($output, ['รวมทั้งหมด:', count($records), 'รายการ']);
    
    // Count items with stock
    $withStock = 0;
    $withPhoto = 0;
    foreach ($records as $record) {
        if ($record['quantity_main'] > 0 || $record['quantity_sub'] > 0) {
            $withStock++;
        }
        if ($record['photo_path'] && $record['photo_path'] !== 'no-photo.jpg') {
            $withPhoto++;
        }
    }
    
    fputcsv($output, ['มีสต็อก:', $withStock, 'รายการ']);
    fputcsv($output, ['ไม่มีสต็อก:', (count($records) - $withStock), 'รายการ']);
    fputcsv($output, ['มีรูปภาพ:', $withPhoto, 'รายการ']);
    fputcsv($output, []); // Empty line
    fputcsv($output, ['ส่งออกเมื่อ:', date('d/m/Y H:i:s')]);
    
    fclose($output);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}
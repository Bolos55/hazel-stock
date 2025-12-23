<?php
require_once __DIR__ . '/../config.php';

try {
    $db = Database::getInstance()->getConnection();
    $date = $_GET['date'] ?? date('Y-m-d');
    
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
    
    if (empty($records)) {
        throw new Exception('ไม่มีข้อมูลสำหรับวันที่เลือก');
    }
    
    // Set headers for CSV download
    $filename = "hazel_stock_" . $date . ".csv";
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
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
    fputcsv($output, ['ลำดับ', 'วัตถุดิบ', 'จำนวนคงเหลือ', 'หน่วย']);
    
    // Data rows
    $i = 1;
    foreach ($records as $record) {
        fputcsv($output, [
            $i++,
            $record['material_name'],
            number_format($record['remaining_quantity'], 2),
            $record['unit']
        ]);
    }
    
    // Summary
    fputcsv($output, []); // Empty line
    fputcsv($output, ['รวม ' . count($records) . ' รายการ']);
    
    fclose($output);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}
<?php
/**
 * API: Export Daily Stock Summary (JSON)
 * ดูข้อมูลสต็อกรายวันในรูปแบบ JSON
 */

require_once dirname(__DIR__) . '/config.php';

try {
    // รับพารามิเตอร์
    $date = isset($_GET['date']) ? trim($_GET['date']) : getCurrentDate();
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        errorResponse('รูปแบบวันที่ไม่ถูกต้อง ใช้ YYYY-MM-DD', 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Query ข้อมูลสต็อก
    $stmt = $db->prepare("
        SELECT 
            dsr.id,
            dsr.record_date,
            rm.material_name,
            dsr.remaining_quantity,
            rm.unit,
            dsr.photo_path,
            dsr.employee_name,
            dsr.submitted_at
        FROM daily_stock_records dsr
        INNER JOIN raw_materials rm ON dsr.material_id = rm.id
        WHERE dsr.record_date = ?
        ORDER BY rm.display_order ASC, rm.id ASC
    ");
    
    $stmt->execute([$date]);
    $records = $stmt->fetchAll();
    
    if (empty($records)) {
        jsonResponse([
            'success' => true,
            'date' => $date,
            'has_data' => false,
            'records' => [],
            'summary' => [
                'total_items' => 0,
                'employee_name' => null,
                'submitted_at' => null
            ]
        ]);
    }
    
    // สร้าง base URL สำหรับรูปภาพ
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptPath = dirname($_SERVER['PHP_SELF']);
    $baseUrl = $protocol . '://' . $host . dirname($scriptPath);
    
    // เพิ่ม full URL ให้กับรูปภาพ
    foreach ($records as &$record) {
        $record['photo_url'] = $baseUrl . '/stock-photos/' . $record['photo_path'];
    }
    
    jsonResponse([
        'success' => true,
        'date' => $date,
        'has_data' => true,
        'records' => $records,
        'summary' => [
            'total_items' => count($records),
            'employee_name' => $records[0]['employee_name'],
            'submitted_at' => $records[0]['submitted_at']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in export-daily-stock.php: ' . $e->getMessage());
    errorResponse('เกิดข้อผิดพลาดในการดึงข้อมูล', 500, $e->getMessage());
    
} catch (Exception $e) {
    error_log('Error in export-daily-stock.php: ' . $e->getMessage());
    errorResponse('เกิดข้อผิดพลาด', 500, $e->getMessage());
}
?>

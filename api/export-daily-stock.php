<?php
/**
 * Daily Excel Export Script (Cron Job)
 * แก้ไขแล้ว: เพิ่ม error handling, logging และการจัดการที่ดีขึ้น
 * 
 * วิธีตั้งค่า Cron Job:
 * crontab -e
 * เพิ่มบรรทัด: 55 23 * * * /usr/bin/php /path/to/cron/daily-excel-export.php >> /path/to/logs/cron.log 2>&1
 */

// กำหนดเวลาเริ่มต้น
$startTime = microtime(true);
$scriptPath = dirname(__DIR__);

require_once $scriptPath . '/config.php';

// ตรวจสอบว่ามี PhpSpreadsheet
if (!file_exists($scriptPath . '/vendor/autoload.php')) {
    error_log('CRON ERROR: PhpSpreadsheet not installed');
    echo "ERROR: PhpSpreadsheet not installed. Run: composer require phpoffice/phpspreadsheet\n";
    exit(1);
}

require_once $scriptPath . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

echo "========================================\n";
echo "Daily Excel Export Script\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n\n";

try {
    $today = getCurrentDate();
    echo "Processing date: $today\n";
    
    $db = Database::getInstance()->getConnection();
    echo "Database connected successfully\n";
    
    // Query ข้อมูล
    $stmt = $db->prepare("
        SELECT 
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
    
    $stmt->execute([$today]);
    $records = $stmt->fetchAll();
    
    $recordCount = count($records);
    echo "Found $recordCount records\n";
    
    if (empty($records)) {
        echo "WARNING: No stock records found for $today\n";
        
        // บันทึก log ลงฐานข้อมูล
        $logStmt = $db->prepare("
            INSERT INTO excel_export_log (export_date, file_path, records_count, status, error_message)
            VALUES (?, '', 0, 'failed', 'No stock records found')
        ");
        $logStmt->execute([$today]);
        
        logActivity('Excel export failed - no records', ['date' => $today]);
        
        echo "\nScript completed with warning\n";
        exit(1);
    }
    
    echo "Creating Excel file...\n";
    
    // สร้าง Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('บันทึกสต็อก');
    
    // ตั้งค่าคุณสมบัติไฟล์
    $spreadsheet->getProperties()
        ->setCreator('Restaurant Stock System')
        ->setTitle("Stock Record - $today")
        ->setSubject('Daily Stock Recording')
        ->setDescription("Daily stock record for $today")
        ->setKeywords('stock inventory restaurant')
        ->setCategory('Reports');
    
    // ตั้งค่าความกว้างคอลัมน์
    $sheet->getColumnDimension('A')->setWidth(15);
    $sheet->getColumnDimension('B')->setWidth(30);
    $sheet->getColumnDimension('C')->setWidth(15);
    $sheet->getColumnDimension('D')->setWidth(10);
    $sheet->getColumnDimension('E')->setWidth(60);
    $sheet->getColumnDimension('F')->setWidth(20);
    $sheet->getColumnDimension('G')->setWidth(20);
    
    // สไตล์ header
    $headerStyle = [
        'font' => [
            'bold' => true,
            'size' => 12,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4472C4']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ]
    ];
    
    // ตั้งค่า header
    $headers = ['วันที่', 'รายการวัตถุดิบ', 'ปริมาณคงเหลือ', 'หน่วย', 'รูปภาพ', 'บันทึกโดย', 'เวลาบันทึก'];
    $sheet->fromArray($headers, null, 'A1');
    $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
    $sheet->getRowDimension(1)->setRowHeight(30);
    
    // สไตล์ข้อมูล
    $dataStyle = [
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => false
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'CCCCCC']
            ]
        ]
    ];
    
    // สร้าง base URL (สำหรับ cron job ใช้ค่าเริ่มต้น)
    $baseUrl = getenv('APP_URL') ?: 'http://localhost';
    $baseUrl = rtrim($baseUrl, '/');
    
    // เพิ่มข้อมูล
    $row = 2;
    foreach ($records as $record) {
        // แปลงวันที่เป็นรูปแบบไทย
        $dateObj = new DateTime($record['record_date']);
        $thaiYear = (int)$dateObj->format('Y') + 543;
        $thaiDate = $dateObj->format('d/m/') . $thaiYear;
        
        // แปลงเวลา
        $timeObj = new DateTime($record['submitted_at']);
        $thaiTime = $timeObj->format('H:i:s');
        
        // URL รูปภาพ
        $photoUrl = $baseUrl . '/stock-photos/' . $record['photo_path'];
        
        // ตั้งค่าข้อมูลในแต่ละเซลล์
        $sheet->setCellValue("A$row", $thaiDate);
        $sheet->setCellValue("B$row", $record['material_name']);
        $sheet->setCellValue("C$row", $record['remaining_quantity']);
        $sheet->setCellValue("D$row", $record['unit']);
        $sheet->setCellValue("E$row", $photoUrl);
        $sheet->setCellValue("F$row", $record['employee_name']);
        $sheet->setCellValue("G$row", $thaiTime);
        
        // ทำให้ URL คลิกได้
        $sheet->getCell("E$row")->getHyperlink()->setUrl($photoUrl);
        $sheet->getStyle("E$row")->getFont()
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0000FF'))
            ->setUnderline(Font::UNDERLINE_SINGLE);
        
        // ใช้สไตล์
        $sheet->getStyle("A$row:G$row")->applyFromArray($dataStyle);
        $sheet->getRowDimension($row)->setRowHeight(25);
        
        // จัดแนว
        $sheet->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C$row:D$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row++;
    }
    
    // แถวสรุป
    $summaryRow = $row + 1;
    $sheet->setCellValue("A$summaryRow", "สรุป");
    $sheet->setCellValue("B$summaryRow", "จำนวนรายการทั้งหมด:");
    $sheet->setCellValue("C$summaryRow", count($records));
    $sheet->setCellValue("D$summaryRow", "รายการ");
    
    $summaryStyle = [
        'font' => ['bold' => true, 'size' => 11],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'F2F2F2']
        ],
        'borders' => [
            'top' => [
                'borderStyle' => Border::BORDER_MEDIUM,
                'color' => ['rgb' => '000000']
            ]
        ]
    ];
    $sheet->getStyle("A$summaryRow:G$summaryRow")->applyFromArray($summaryStyle);
    
    // สร้าง directory ถ้ายังไม่มี
    if (!file_exists(EXCEL_DIR)) {
        mkdir(EXCEL_DIR, 0755, true);
        echo "Created excel-exports directory\n";
    }
    
    // ชื่อไฟล์
    $filename = "stock_$today.xlsx";
    $filepath = EXCEL_DIR . '/' . $filename;
    
    echo "Saving Excel file to: $filepath\n";
    
    // บันทึกไฟล์
    $writer = new Xlsx($spreadsheet);
    $writer->save($filepath);
    
    // ตรวจสอบว่าไฟล์ถูกสร้างจริง
    if (!file_exists($filepath)) {
        throw new Exception("Failed to create Excel file");
    }
    
    $fileSize = filesize($filepath);
    echo "Excel file created successfully\n";
    echo "File size: " . round($fileSize / 1024, 2) . " KB\n";
    
    // บันทึก log ลงฐานข้อมูล
    $logStmt = $db->prepare("
        INSERT INTO excel_export_log (export_date, file_path, records_count, status)
        VALUES (?, ?, ?, 'success')
    ");
    $logStmt->execute([$today, $filename, count($records)]);
    
    // คำนวณเวลาที่ใช้
    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);
    
    echo "\n========================================\n";
    echo "Excel export completed successfully!\n";
    echo "Date: $today\n";
    echo "Records: $recordCount\n";
    echo "File: $filename\n";
    echo "Execution time: {$executionTime} seconds\n";
    echo "========================================\n";
    
    // Log activity
    logActivity('Excel exported successfully', [
        'date' => $today,
        'records_count' => $recordCount,
        'filename' => $filename,
        'file_size' => $fileSize,
        'execution_time' => $executionTime
    ]);
    
    exit(0);
    
} catch (PDOException $e) {
    $errorMsg = 'Database error: ' . $e->getMessage();
    error_log("CRON ERROR: $errorMsg");
    echo "ERROR: $errorMsg\n";
    
    // บันทึก error log
    try {
        $db = Database::getInstance()->getConnection();
        $logStmt = $db->prepare("
            INSERT INTO excel_export_log (export_date, file_path, records_count, status, error_message)
            VALUES (?, '', 0, 'failed', ?)
        ");
        $logStmt->execute([$today ?? date('Y-m-d'), $errorMsg]);
    } catch (Exception $logError) {
        error_log("Failed to log error: " . $logError->getMessage());
    }
    
    logActivity('Excel export failed - database error', [
        'error' => $errorMsg,
        'date' => $today ?? date('Y-m-d')
    ]);
    
    exit(1);
    
} catch (Exception $e) {
    $errorMsg = $e->getMessage();
    error_log("CRON ERROR: $errorMsg");
    echo "ERROR: $errorMsg\n";
    
    // บันทึก error log
    try {
        $db = Database::getInstance()->getConnection();
        $logStmt = $db->prepare("
            INSERT INTO excel_export_log (export_date, file_path, records_count, status, error_message)
            VALUES (?, '', 0, 'failed', ?)
        ");
        $logStmt->execute([$today ?? date('Y-m-d'), $errorMsg]);
    } catch (Exception $logError) {
        error_log("Failed to log error: " . $logError->getMessage());
    }
    
    logActivity('Excel export failed', [
        'error' => $errorMsg,
        'date' => $today ?? date('Y-m-d')
    ]);
    
    exit(1);
}
?>

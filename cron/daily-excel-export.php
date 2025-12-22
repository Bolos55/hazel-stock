<?php
/**
 * Daily Excel Export Script
 * Run this via cron job at 23:55 every day
 * Cron: 55 23 * * * /usr/bin/php /path/to/cron/daily-excel-export.php
 */

require_once dirname(__DIR__) . '/config.php';

// Check if PhpSpreadsheet is available
if (!file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    echo "PhpSpreadsheet not installed. Please run: composer install\n";
    exit(1);
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {
    $today = getCurrentDate();
    $db = Database::getInstance()->getConnection();
    
    // Fetch today's stock records with material details - แก้ไข column names
    $stmt = $db->prepare("
        SELECT 
            dsr.record_date,
            rm.material_name,
            dsr.remaining_quantity,
            rm.unit,
            dsr.photo_path,
            e.full_name as employee_name,
            dsr.submitted_at
        FROM daily_stock_records dsr
        INNER JOIN raw_materials rm ON dsr.material_id = rm.id
        LEFT JOIN employees e ON dsr.employee_id = e.id
        WHERE dsr.record_date = ?
        ORDER BY rm.display_order ASC, rm.id ASC
    ");
    
    $stmt->execute([$today]);
    $records = $stmt->fetchAll();
    
    if (empty($records)) {
        // Log: No records for today
        error_log("Excel Export: No stock records found for {$today}");
        
        // Log to database
        $logStmt = $db->prepare("
            INSERT INTO excel_export_log (export_date, file_path, records_count, status, error_message)
            VALUES (?, '', 0, 'failed', 'No stock records found')
        ");
        $logStmt->execute([$today]);
        
        exit(1);
    }
    
    // Create new Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set sheet title
    $sheet->setTitle('Stock Record');
    
    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(15);
    $sheet->getColumnDimension('B')->setWidth(25);
    $sheet->getColumnDimension('C')->setWidth(15);
    $sheet->getColumnDimension('D')->setWidth(10);
    $sheet->getColumnDimension('E')->setWidth(50);
    $sheet->getColumnDimension('F')->setWidth(20);
    $sheet->getColumnDimension('G')->setWidth(20);
    
    // Header row styling
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
    
    // Set headers
    $headers = ['วันที่', 'รายการวัตถุดิบ', 'ปริมาณคงเหลือ', 'หน่วย', 'รูปภาพ', 'บันทึกโดย', 'เวลาบันทึก'];
    $sheet->fromArray($headers, NULL, 'A1');
    $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
    $sheet->getRowDimension(1)->setRowHeight(25);
    
    // Data styling
    $dataStyle = [
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'CCCCCC']
            ]
        ]
    ];
    
    // Add data rows
    $row = 2;
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") 
                . "://{$_SERVER['HTTP_HOST']}" 
                . dirname(dirname($_SERVER['PHP_SELF']));
    
    foreach ($records as $record) {
        // Format date in Thai
        $dateObj = new DateTime($record['record_date']);
        $thaiDate = $dateObj->format('d/m/') . ($dateObj->format('Y') + 543);
        
        // Format submitted time
        $timeObj = new DateTime($record['submitted_at']);
        $thaiTime = $timeObj->format('H:i:s');
        
        // Photo URL (clickable link)
        $photoUrl = $baseUrl . '/stock-photos/' . $record['photo_path'];
        
        // Set cell values
        $sheet->setCellValue("A{$row}", $thaiDate);
        $sheet->setCellValue("B{$row}", $record['material_name']);
        $sheet->setCellValue("C{$row}", $record['remaining_quantity']);
        $sheet->setCellValue("D{$row}", $record['unit']);
        $sheet->setCellValue("E{$row}", $photoUrl);
        $sheet->setCellValue("F{$row}", $record['employee_name'] ?? '-');
        $sheet->setCellValue("G{$row}", $thaiTime);
        
        // Make photo URL clickable
        if (!empty($record['photo_path'])) {
            $sheet->getCell("E{$row}")->getHyperlink()->setUrl($photoUrl);
            $sheet->getStyle("E{$row}")->getFont()->getColor()->setRGB('0000FF');
            $sheet->getStyle("E{$row}")->getFont()->setUnderline(true);
        }
        
        // Apply data styling
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($dataStyle);
        $sheet->getRowDimension($row)->setRowHeight(20);
        
        // Center align quantity and unit
        $sheet->getStyle("C{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row++;
    }
    
    // Add summary row
    $summaryRow = $row + 1;
    $sheet->setCellValue("A{$summaryRow}", "สรุป");
    $sheet->setCellValue("B{$summaryRow}", "จำนวนรายการทั้งหมด:");
    $sheet->setCellValue("C{$summaryRow}", count($records));
    $sheet->getStyle("A{$summaryRow}:G{$summaryRow}")->getFont()->setBold(true);
    
    // Generate filename
    $filename = "stock_{$today}.xlsx";
    $filepath = EXCEL_DIR . '/' . $filename;
    
    // Save Excel file
    $writer = new Xlsx($spreadsheet);
    $writer->save($filepath);
    
    // Log successful export
    $logStmt = $db->prepare("
        INSERT INTO excel_export_log (export_date, file_path, records_count, status)
        VALUES (?, ?, ?, 'success')
    ");
    $logStmt->execute([$today, $filename, count($records)]);
    
    echo "Excel export successful: {$filename}\n";
    echo "Records exported: " . count($records) . "\n";
    
    exit(0);
    
} catch (Exception $e) {
    error_log("Excel Export Error: " . $e->getMessage());
    
    // Log failed export
    try {
        $db = Database::getInstance()->getConnection();
        $logStmt = $db->prepare("
            INSERT INTO excel_export_log (export_date, file_path, records_count, status, error_message)
            VALUES (?, '', 0, 'failed', ?)
        ");
        $logStmt->execute([$today ?? date('Y-m-d'), $e->getMessage()]);
    } catch (Exception $logError) {
        error_log("Failed to log error: " . $logError->getMessage());
    }
    
    exit(1);
}
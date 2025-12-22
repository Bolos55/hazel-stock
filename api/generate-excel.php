<?php
/**
 * Manual Excel Generation for Testing
 * URL: http://localhost/hazel-stock/api/generate-excel.php?date=2025-12-17
 */

require_once dirname(__DIR__) . '/config.php';

// Check if PhpSpreadsheet is available
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    http_response_code(500);
    echo 'Error: PhpSpreadsheet not installed. Please run: composer install';
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {
    // วันที่ (YYYY-MM-DD)
    $date = $_GET['date'] ?? getCurrentDate();

    // เพิ่ม date validation
    if (!validateDate($date)) {
        throw new Exception('Invalid date format (YYYY-MM-DD)');
    }

    $pdo = Database::getInstance()->getConnection();

    // ✅ SQL ที่ตรงกับโครงสร้าง DB จริง
    $stmt = $pdo->prepare("
        SELECT 
            d.record_date,
            r.material_name,
            d.remaining_quantity,
            r.unit,
            d.photo_path,
            e.full_name as employee_name,
            d.submitted_at
        FROM daily_stock_records d
        JOIN raw_materials r ON d.material_id = r.id
        LEFT JOIN employees e ON d.employee_id = e.id
        WHERE d.record_date = ?
        ORDER BY r.display_order ASC, r.id ASC
    ");
    $stmt->execute([$date]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$records) {
        throw new Exception("No stock records found for {$date}");
    }

    /* ================= Excel ================= */

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Daily Stock');

    // ความกว้างคอลัมน์
    $widths = [15, 28, 18, 10, 55, 20, 18];
    foreach (range('A', 'G') as $i => $col) {
        $sheet->getColumnDimension($col)->setWidth($widths[$i]);
    }

    // Header style
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];

    // Header
    $headers = ['วันที่', 'วัตถุดิบ', 'คงเหลือ', 'หน่วย', 'รูปภาพ', 'พนักงาน', 'เวลาบันทึก'];
    $sheet->fromArray($headers, null, 'A1');
    $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
    $sheet->getRowDimension(1)->setRowHeight(26);

    // Data style
    $dataStyle = [
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
    ];

    $row = 2;
    $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
        . '://' . $_SERVER['HTTP_HOST']
        . '/hazel-stock/stock-photos/';

    foreach ($records as $r) {
        $thaiDate = date('d/m/', strtotime($r['record_date']))
            . (date('Y', strtotime($r['record_date'])) + 543);

        $sheet->setCellValue("A{$row}", $thaiDate);
        $sheet->setCellValue("B{$row}", $r['material_name']);
        $sheet->setCellValue("C{$row}", $r['remaining_quantity']);
        $sheet->setCellValue("D{$row}", $r['unit']);

        if ($r['photo_path']) {
            $photoUrl = $baseUrl . $r['photo_path'];
            $sheet->setCellValue("E{$row}", $photoUrl);
            $sheet->getCell("E{$row}")->getHyperlink()->setUrl($photoUrl);
            $sheet->getStyle("E{$row}")->getFont()->getColor()->setRGB('0000FF');
            $sheet->getStyle("E{$row}")->getFont()->setUnderline(true);
        }

        $sheet->setCellValue("F{$row}", $r['employee_name'] ?? '-');
        $sheet->setCellValue("G{$row}", date('H:i:s', strtotime($r['submitted_at'])));

        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($dataStyle);
        $sheet->getStyle("C{$row}:D{$row}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
    }

    // สรุป
    $summaryRow = $row + 1;
    $sheet->setCellValue("B{$summaryRow}", "รวมทั้งหมด:");
    $sheet->setCellValue("C{$summaryRow}", count($records));
    $sheet->getStyle("B{$summaryRow}:C{$summaryRow}")->getFont()->setBold(true);

    // ส่งไฟล์
    $filename = "daily_stock_{$date}.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    header('Cache-Control: max-age=0');

    (new Xlsx($spreadsheet))->save('php://output');
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo "Excel error: " . htmlspecialchars($e->getMessage());
}

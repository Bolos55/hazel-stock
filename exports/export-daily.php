<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$db = Database::getInstance()->getConnection();

$today = $_GET['date'] ?? date('Y-m-d');

// ดึงข้อมูล
$stmt = $db->prepare("
    SELECT 
        d.record_date,
        e.full_name AS employee_name,
        r.material_name,
        r.unit,
        d.quantity_remaining,
        d.photo_path
    FROM daily_stock_records d
    JOIN employees e ON d.employee_id = e.id
    JOIN raw_materials r ON d.material_id = r.id
    WHERE d.record_date = ?
    ORDER BY r.material_name
");
$stmt->execute([$today]);
$rows = $stmt->fetchAll();

if (!$rows) {
    die('ไม่มีข้อมูลสำหรับวันที่เลือก');
}

// สร้าง Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Daily Stock');

// Header
$headers = [
    'วันที่',
    'พนักงาน',
    'วัตถุดิบ',
    'จำนวน',
    'หน่วย',
    'รูป'
];

$sheet->fromArray($headers, null, 'A1');

// Data
$rowNum = 2;
foreach ($rows as $row) {
    $sheet->setCellValue("A{$rowNum}", $row['record_date']);
    $sheet->setCellValue("B{$rowNum}", $row['employee_name']);
    $sheet->setCellValue("C{$rowNum}", $row['material_name']);
    $sheet->setCellValue("D{$rowNum}", $row['quantity_remaining']);
    $sheet->setCellValue("E{$rowNum}", $row['unit']);
    $sheet->setCellValue("F{$rowNum}", $row['photo_path']);
    $rowNum++;
}

// Auto width
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Download
$filename = "daily_stock_{$today}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

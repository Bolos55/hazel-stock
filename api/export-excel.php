<?php
ob_clean(); // ป้องกัน Excel พังจาก output แปลก

require_once __DIR__ . '/../config.php';

// Check if PhpSpreadsheet is available
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    http_response_code(500);
    echo 'Error: PhpSpreadsheet not installed. Please run: composer install';
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../work-date-helper.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

try {
    $db = Database::getInstance()->getConnection();
    $date = $_GET['date'] ?? getWorkDate();

    // เพิ่ม date validation
    if (!validateDate($date)) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD');
    }

    /* ================= วันนี้ ================= */
    $stmt = $db->prepare("
        SELECT 
            ds.material_id,
            ds.remaining_quantity,
            ds.photo_path,
            m.material_name,
            m.unit,
            e.full_name AS employee_name
        FROM daily_stock_records ds
        JOIN raw_materials m ON ds.material_id = m.id
        JOIN employees e ON ds.employee_id = e.id
        WHERE ds.record_date = ?
        ORDER BY m.display_order ASC, m.id ASC
    ");
    $stmt->execute([$date]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        throw new Exception('ไม่มีข้อมูลสำหรับวันที่เลือก');
    }

    /* ================= เมื่อวาน ================= */
    $yesterday = (new DateTime($date))->modify('-1 day')->format('Y-m-d');
    $prevStmt = $db->prepare("
        SELECT material_id, remaining_quantity
        FROM daily_stock_records
        WHERE record_date = ?
    ");
    $prevStmt->execute([$yesterday]);

    $yesterdayData = [];
    foreach ($prevStmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $yesterdayData[(int)$r['material_id']] = (float)$r['remaining_quantity'];
    }

    /* ================= Spreadsheet ================= */
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    /* ================= HEADER ================= */
    $thaiDate = date('d/m/', strtotime($date)) . (date('Y', strtotime($date)) + 543);

    $sheet->mergeCells('A1:F1');
    $sheet->setCellValue('A1', 'Hazel – Beverages & Appetizers');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A2:F2');
    $sheet->setCellValue('A2', "บันทึกสต็อกวัตถุดิบ วันที่ {$thaiDate}");
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A3:F3');
    $sheet->setCellValue('A3', 'พนักงาน: ' . $rows[0]['employee_name']);

    /* ================= TABLE HEADER ================= */
    $sheet->fromArray(
        ['ลำดับ', 'วัตถุดิบ', 'คงเหลือวันนี้', 'ใช้ไปวันนี้', 'หน่วย', 'รูปภาพ'],
        null,
        'A5'
    );

    $sheet->getStyle('A5:F5')->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFF3E0']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER
        ]
    ]);

    /* ================= DATA ================= */
    $rowNum = 6;
    $i = 1;

    foreach ($rows as $r) {
        $materialId = (int)$r['material_id'];
        $todayQty = (float)$r['remaining_quantity'];
        $yesterdayQty = $yesterdayData[$materialId] ?? null;

        $sheet->setCellValue("A{$rowNum}", $i++);
        $sheet->setCellValue("B{$rowNum}", $r['material_name']);
        $sheet->setCellValue("C{$rowNum}", $todayQty);

        // สูตรใช้ไปวันนี้
        if ($yesterdayQty !== null) {
            $sheet->setCellValue("D{$rowNum}", "=MAX(0,{$yesterdayQty}-C{$rowNum})");
        } else {
            $sheet->setCellValue("D{$rowNum}", 0);
        }

        $sheet->setCellValue("E{$rowNum}", $r['unit']);

        /* ===== ฝังรูปจริง ===== */
        $photoPath = __DIR__ . '/../stock-photos/' . ltrim($r['photo_path'], '/');

        if (!empty($r['photo_path']) && is_file($photoPath)) {
            try {
                $drawing = new Drawing();
                $drawing->setName($r['material_name']);
                $drawing->setDescription('Stock Photo');
                $drawing->setPath($photoPath);
                $drawing->setHeight(80);
                $drawing->setCoordinates("F{$rowNum}");
                $drawing->setOffsetX(8);
                $drawing->setOffsetY(6);
                $drawing->setWorksheet($sheet);

                $sheet->getRowDimension($rowNum)->setRowHeight(75);
            } catch (Exception $e) {
                $sheet->setCellValue("F{$rowNum}", 'รูปเสียหาย');
            }
        } else {
            $sheet->setCellValue("F{$rowNum}", 'ไม่มีรูป');
        }

        $rowNum++;
    }

    /* ================= SUMMARY ================= */
    $lastRow = $rowNum - 1;
    $summaryRow = $lastRow + 1;

    $sheet->setCellValue("B{$summaryRow}", 'รวมใช้ไปวันนี้');
    $sheet->setCellValue("D{$summaryRow}", "=SUM(D6:D{$lastRow})");
    $sheet->getStyle("B{$summaryRow}:D{$summaryRow}")
          ->getFont()->setBold(true);

    $sheet->getStyle("B{$summaryRow}:D{$summaryRow}")
          ->getBorders()
          ->getTop()
          ->setBorderStyle(Border::BORDER_THICK);

    /* ================= STYLE ================= */
    $sheet->getStyle("A6:F{$lastRow}")
          ->getBorders()
          ->getAllBorders()
          ->setBorderStyle(Border::BORDER_THIN);

    $sheet->getStyle("A6:F{$lastRow}")
          ->getAlignment()
          ->setVertical(Alignment::VERTICAL_CENTER);

    $sheet->getStyle("C6:D{$lastRow}")
          ->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    foreach (['A'=>6,'B'=>28,'C'=>14,'D'=>14,'E'=>10,'F'=>24] as $col=>$w) {
        $sheet->getColumnDimension($col)->setWidth($w);
    }

    /* ================= EXPORT ================= */
    $filename = "stock_{$date}.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");

    (new Xlsx($spreadsheet))->save('php://output');
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}

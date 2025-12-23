<?php
require_once __DIR__ . '/../config.php';
session_start();

try {
    $db = Database::getInstance()->getConnection();

    $data = json_decode(file_get_contents("php://input"), true);

    $employeeName = trim($data['employee_name'] ?? '');
    $stockData = $data['stock_data'] ?? [];

    // แก้ไข syntax error - ย้าย jsonResponse เข้าไปใน if block
    if (!is_array($stockData) || count($stockData) === 0) {
        jsonResponse([
            'success' => false,
            'message' => 'ไม่มีข้อมูลสต็อก'
        ], 400);
    }

    if ($employeeName === '' || empty($stockData)) {
        jsonResponse(['success' => false, 'message' => 'ข้อมูลไม่ครบ'], 400);
    }

    // หา employee หรือสร้างใหม่
    $stmt = $db->prepare("SELECT id FROM employees WHERE full_name = ?");
    $stmt->execute([$employeeName]);
    $employee = $stmt->fetch();

    if (!$employee) {
        // สร้างพนักงานใหม่
        $stmt = $db->prepare("
            INSERT INTO employees (full_name, employee_name) 
            VALUES (?, ?)
        ");
        $stmt->execute([$employeeName, $employeeName]);
        $employeeId = $db->lastInsertId();
    } else {
        $employeeId = $employee['id'];
    }
    $today = date('Y-m-d');

    // เช็กว่าบันทึกวันนี้แล้วหรือยัง
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM daily_stock_records 
        WHERE record_date = ?
    ");
    $stmt->execute([$today]);

    if ($stmt->fetchColumn() > 0) {
        jsonResponse(['success' => false, 'message' => 'วันนี้บันทึกไปแล้ว'], 409);
    }

    // เริ่ม transaction
    $db->beginTransaction();

    // แก้ไข column name และเพิ่ม submitted_at
    $stmt = $db->prepare("
        INSERT INTO daily_stock_records
        (record_date, employee_id, material_id, remaining_quantity, photo_path, submitted_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");

    foreach ($stockData as $item) {
        if (
            empty($item['material_id']) ||
            !isset($item['quantity'])
        ) {
            throw new Exception('ข้อมูลวัตถุดิบไม่ครบ');
        }

        if ($item['quantity'] <= 0) {
            continue; // Skip items with 0 quantity
        }

        // ตรวจสอบว่า material_id มีอยู่จริง
        $checkStmt = $db->prepare("SELECT material_name FROM raw_materials WHERE id = ?");
        $checkStmt->execute([$item['material_id']]);
        $material = $checkStmt->fetch();
        if (!$material) {
            throw new Exception('ไม่พบวัตถุดิบ ID: ' . $item['material_id']);
        }

        // Handle photo upload
        $photoPath = 'no-photo.jpg';
        if (!empty($item['photo']) && $item['photo'] !== 'no-photo.jpg') {
            try {
                // Decode base64 image
                if (preg_match('/^data:image\/(\w+);base64,/', $item['photo'], $matches)) {
                    $imageType = $matches[1];
                    $imageData = substr($item['photo'], strpos($item['photo'], ',') + 1);
                    $imageData = base64_decode($imageData);
                    
                    if ($imageData === false) {
                        throw new Exception('Invalid image data');
                    }
                    
                    // Create today's photo directory
                    $todayDir = PHOTOS_DIR . '/' . $today;
                    if (!is_dir($todayDir)) {
                        mkdir($todayDir, 0755, true);
                    }
                    
                    // Generate filename
                    $safeName = preg_replace('/[^a-zA-Z0-9ก-๙]/u', '-', $material['material_name']);
                    $fileName = $safeName . '-' . time() . '-' . $item['material_id'] . '.jpg';
                    $filePath = $todayDir . '/' . $fileName;
                    
                    // Save image
                    if (file_put_contents($filePath, $imageData) !== false) {
                        $photoPath = $today . '/' . $fileName;
                    }
                }
            } catch (Exception $photoError) {
                // Log photo error but continue with submission
                error_log('Photo upload error: ' . $photoError->getMessage());
            }
        }

        $stmt->execute([
            $today,
            $employeeId,
            $item['material_id'],
            $item['quantity'],
            $photoPath
        ]);
    }

    $db->commit();

    jsonResponse(['success' => true]);

} catch (PDOException $e) {
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }

    // Duplicate entry (unique constraint)
    if ($e->getCode() === '23000') {
        jsonResponse([
            'success' => false,
            'message' => 'ข้อมูลวันนี้ถูกบันทึกแล้ว'
        ], 409);
    }

    jsonResponse([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ], 500);

} catch (Throwable $e) {
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }

    jsonResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 500);
}

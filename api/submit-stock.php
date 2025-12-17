<?php
require_once __DIR__ . '/../config.php';
session_start();

try {
    $db = Database::getInstance()->getConnection();

    $data = json_decode(file_get_contents("php://input"), true);

    $employeeName = trim($data['employee_name'] ?? '');
    $stockData = $data['stock_data'] ?? [];

    if (!is_array($stockData) || count($stockData) === 0) {
    jsonResponse([
        'success' => false,
        'message' => 'ไม่มีข้อมูลสต็อก'
    ], 400);
}

    if ($employeeName === '' || empty($stockData)) {
        jsonResponse(['success' => false, 'message' => 'ข้อมูลไม่ครบ'], 400);
    }

    // หา employee
    $stmt = $db->prepare("SELECT id FROM employees WHERE full_name = ?");
    $stmt->execute([$employeeName]);
    $employee = $stmt->fetch();

    if (!$employee) {
        jsonResponse(['success' => false, 'message' => 'ไม่พบพนักงาน'], 404);
    }

    $employeeId = $employee['id'];
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

    $stmt = $db->prepare("
        INSERT INTO daily_stock_records
        (record_date, employee_id, material_id, quantity_remaining, photo_path)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($stockData as $item) {
         if (
                empty($item['material_id']) ||
                !isset($item['quantity']) ||
                empty($item['photo'])
            ) {
                throw new Exception('ข้อมูลวัตถุดิบไม่ครบ');
            }

            if ($item['quantity'] <= 0) {
                throw new Exception('จำนวนต้องมากกว่า 0');
            }

            $stmt->execute([
                $today,
                $employeeId,
                $item['material_id'],
                $item['quantity'],
                $item['photo']
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
        'message' => 'Database error'
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

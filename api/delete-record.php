<?php
require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }
    
    $data = json_decode(file_get_contents("php://input"), true);
    $date = $data['date'] ?? '';
    
    // Validate date
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        throw new Exception('Invalid date format');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Check if records exist
    $stmt = $db->prepare("SELECT COUNT(*) FROM daily_stock_records WHERE record_date = ?");
    $stmt->execute([$date]);
    $recordCount = $stmt->fetchColumn();
    
    if ($recordCount == 0) {
        throw new Exception('ไม่พบข้อมูลสำหรับวันที่นี้');
    }
    
    // Get photo paths before deletion
    $stmt = $db->prepare("SELECT photo_path FROM daily_stock_records WHERE record_date = ? AND photo_path IS NOT NULL AND photo_path != 'no-photo.jpg'");
    $stmt->execute([$date]);
    $photoPaths = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Delete records
    $stmt = $db->prepare("DELETE FROM daily_stock_records WHERE record_date = ?");
    $stmt->execute([$date]);
    
    $deletedCount = $stmt->rowCount();
    
    // Try to delete photo files (optional - don't fail if can't delete)
    $deletedPhotos = 0;
    foreach ($photoPaths as $photoPath) {
        $fullPath = __DIR__ . '/../stock-photos/' . $photoPath;
        if (file_exists($fullPath) && unlink($fullPath)) {
            $deletedPhotos++;
        }
    }
    
    jsonResponse([
        'success' => true,
        'message' => "ลบข้อมูลสำเร็จ",
        'details' => [
            'deleted_records' => $deletedCount,
            'deleted_photos' => $deletedPhotos,
            'date' => $date
        ]
    ]);
    
} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 400);
}
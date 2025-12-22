<?php
require_once '../config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No photo uploaded');
    }

    $photo = $_FILES['photo'];
    $materialId = $_POST['material_id'] ?? null;
    $materialName = $_POST['material_name'] ?? null;

    if (!$materialId || !$materialName) {
        throw new Exception('Missing material information');
    }

    // ตรวจสอบว่า material_id มีอยู่จริง
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id FROM raw_materials WHERE id = ?");
    $stmt->execute([$materialId]);
    if (!$stmt->fetch()) {
        throw new Exception('Material ID not found');
    }

    if ($photo['size'] > MAX_PHOTO_SIZE) {
        throw new Exception('File too large (max 5MB)');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $photo['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_PHOTO_TYPES, true)) {
        throw new Exception('Invalid file type. Only JPEG and PNG allowed.');
    }

    $todayDir = getTodayPhotoDir();

    $extension = $mimeType === 'image/png' ? 'png' : 'jpg';
    // ป้องกัน path traversal
    $safeName = preg_replace('/[^a-zA-Z0-9ก-๙]/u', '-', basename($materialName));
    $fileName = "{$safeName}-" . time() . ".{$extension}";
    $filePath = "{$todayDir}/{$fileName}";

    if (!move_uploaded_file($photo['tmp_name'], $filePath)) {
        throw new Exception('Failed to save photo');
    }

    jsonResponse([
        'success' => true,
        'photo_path' => getCurrentDate() . '/' . $fileName
    ]);

} catch (Throwable $e) {
    jsonResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 400);
}

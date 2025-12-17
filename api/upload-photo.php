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

    if ($photo['size'] > MAX_PHOTO_SIZE) {
        throw new Exception('File too large (max 5MB)');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $photo['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_PHOTO_TYPES, true)) {
        throw new Exception('Invalid file type');
    }

    $todayDir = getTodayPhotoDir();

    $extension = $mimeType === 'image/png' ? 'png' : 'jpg';
    $safeName = preg_replace('/[^a-zA-Z0-9ก-๙]/u', '-', $materialName);
    $fileName = "{$safeName}-" . time() . ".{$extension}";
    $filePath = "{$todayDir}/{$fileName}";

    if (!move_uploaded_file($photo['tmp_name'], $filePath)) {
        throw new Exception('Failed to save photo');
    }

    echo json_encode([
        'success' => true,
        'photo_path' => getCurrentDate() . '/' . $fileName
    ]);
    exit;

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

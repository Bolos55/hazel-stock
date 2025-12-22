<?php
require_once '../config.php';

$db = Database::getInstance()->getConnection();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code  = trim($_POST['material_code'] ?? '');
    $name  = trim($_POST['material_name'] ?? '');
    $unit  = trim($_POST['unit'] ?? '');
    $stock = floatval($_POST['current_stock'] ?? 0);

    // เพิ่ม validation ที่ดีขึ้น
    if (empty($code) || empty($name) || empty($unit)) {
        $message = "⚠️ กรุณากรอกข้อมูลให้ครบ";
    } elseif (strlen($code) > 50) {
        $message = "⚠️ รหัสวัตถุดิบยาวเกิน 50 ตัวอักษร";
    } elseif (strlen($name) > 255) {
        $message = "⚠️ ชื่อวัตถุดิบยาวเกิน 255 ตัวอักษร";
    } elseif (strlen($unit) > 50) {
        $message = "⚠️ หน่วยยาวเกิน 50 ตัวอักษร";
    } elseif ($stock < 0) {
        $message = "⚠️ สต็อกต้องไม่ติดลบ";
    } else {
        try {
            // ตรวจสอบว่ามี material_code ซ้ำหรือไม่
            $checkStmt = $db->prepare("SELECT id FROM raw_materials WHERE material_code = ?");
            $checkStmt->execute([$code]);
            if ($checkStmt->fetch()) {
                $message = "⚠️ รหัสวัตถุดิบนี้มีอยู่แล้ว";
            } else {
                $stmt = $db->prepare("
                    INSERT INTO raw_materials
                    (material_code, material_name, unit, current_stock)
                    VALUES (:code, :name, :unit, :stock)
                ");
                $stmt->execute([
                    ':code'  => $code,
                    ':name'  => $name,
                    ':unit'  => $unit,
                    ':stock' => $stock
                ]);
                $message = "✅ เพิ่มวัตถุดิบสำเร็จ";
                
                // Clear form data after success
                $code = $name = $unit = '';
                $stock = 0;
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $message = "⚠️ รหัสวัตถุดิบนี้มีอยู่แล้ว";
            } else {
                $message = "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มวัตถุดิบ</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">

<h3 class="mb-3">เพิ่มวัตถุดิบ</h3>

<?php if ($message): ?>
<div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="post">
    <div class="mb-2">
        <label>รหัสวัตถุดิบ</label>
        <input type="text" name="material_code" class="form-control" 
               value="<?= htmlspecialchars($code ?? '') ?>" 
               maxlength="50" required>
    </div>
    <div class="mb-2">
        <label>ชื่อวัตถุดิบ</label>
        <input type="text" name="material_name" class="form-control" 
               value="<?= htmlspecialchars($name ?? '') ?>" 
               maxlength="255" required>
    </div>
    <div class="mb-2">
        <label>หน่วย</label>
        <input type="text" name="unit" class="form-control" 
               value="<?= htmlspecialchars($unit ?? '') ?>" 
               placeholder="กก / ชิ้น" maxlength="50" required>
    </div>
    <div class="mb-3">
        <label>สต็อกเริ่มต้น</label>
        <input type="number" step="0.01" name="current_stock" class="form-control" 
               value="<?= htmlspecialchars($stock ?? 0) ?>" min="0">
    </div>
    <button class="btn btn-primary">บันทึก</button>
    <a href="../" class="btn btn-secondary">กลับหน้าหลัก</a>
</form>

</body>
</html>

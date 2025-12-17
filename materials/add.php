<?php
require_once '../config.php';

$db = Database::getInstance()->getConnection();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code  = trim($_POST['material_code']);
    $name  = trim($_POST['material_name']);
    $unit  = trim($_POST['unit']);
    $stock = floatval($_POST['current_stock']);

    if ($code && $name && $unit) {
        try {
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
        } catch (PDOException $e) {
            $message = "❌ Error: " . $e->getMessage();
        }
    } else {
        $message = "⚠️ กรุณากรอกข้อมูลให้ครบ";
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
        <input type="text" name="material_code" class="form-control" required>
    </div>
    <div class="mb-2">
        <label>ชื่อวัตถุดิบ</label>
        <input type="text" name="material_name" class="form-control" required>
    </div>
    <div class="mb-2">
        <label>หน่วย</label>
        <input type="text" name="unit" class="form-control" placeholder="กก / ชิ้น" required>
    </div>
    <div class="mb-3">
        <label>สต็อกเริ่มต้น</label>
        <input type="number" step="0.01" name="current_stock" class="form-control" value="0">
    </div>
    <button class="btn btn-primary">บันทึก</button>
</form>

</body>
</html>

<?php
require_once '../config.php';

$db = Database::getInstance()->getConnection();
$stmt = $db->query("
    SELECT id, material_name, unit
    FROM raw_materials
    ORDER BY display_order, material_name
");

jsonResponse([
    'success' => true,
    'materials' => $stmt->fetchAll()
]);

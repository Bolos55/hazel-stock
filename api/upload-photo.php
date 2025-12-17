<?php
require_once '../config.php';

$dir = __DIR__ . '/../stock-photos/' . date('Y-m-d');
@mkdir($dir, 0755, true);

$file = $_FILES['photo'];
$name = time() . '-' . basename($file['name']);
$path = "$dir/$name";

move_uploaded_file($file['tmp_name'], $path);

jsonResponse([
    'success' => true,
    'photo_path' => 'stock-photos/' . date('Y-m-d') . '/' . $name
]);
